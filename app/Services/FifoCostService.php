<?php

namespace App\Services;

use App\Models\Branch;
use App\Models\InventoryCostConsumption;
use App\Models\InventoryCostLayer;
use App\Models\Product;
use App\Models\UnitConversion;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class FifoCostService
{
    public function stockQty(Product $product, $qty, $uomId): float
    {
        $qty = (int) $qty;

        if ($uomId && $product->uom_id != $uomId) {
            $conversion = UnitConversion::where([
                'from_unit_id' => $product->uom_id,
                'to_unit_id' => $uomId,
            ])->first();

            if (empty($conversion)) {
                $conversion = UnitConversion::where([
                    'to_unit_id' => $product->uom_id,
                    'from_unit_id' => $uomId,
                ])->first();

                $qty = $qty * $conversion->value;
            } else {
                $qty = $qty / $conversion->value;
            }
        }

        return (float) $qty;
    }

    public function receive(Branch $branch, Product $product, float $stockQty, float $unitCost, string $sourceType, $sourceId, $receivedAt = null): void
    {
        if ($stockQty <= 0) {
            return;
        }

        DB::transaction(function () use ($branch, $product, $stockQty, $unitCost, $sourceType, $sourceId, $receivedAt) {
            InventoryCostLayer::create([
                'branch_id' => $branch->id,
                'product_id' => $product->id,
                'source_type' => $sourceType,
                'source_id' => $sourceId,
                'qty_received' => $stockQty,
                'qty_remaining' => $stockQty,
                'unit_cost' => $unitCost,
                'received_at' => $receivedAt ?: now(),
            ]);

            $this->syncBranchCost($branch, $product);
        });
    }

    public function receiveDocument(Branch $branch, Product $product, $documentQty, $uomId, float $lineUnitPrice, string $sourceType, $sourceId): void
    {
        $documentQty = (int) $documentQty;
        $stockQty = $this->stockQty($product, $documentQty, $uomId);
        if ($stockQty <= 0) {
            return;
        }

        $unitCost = ($documentQty * $lineUnitPrice) / $stockQty;
        $this->receive($branch, $product, $stockQty, $unitCost, $sourceType, $sourceId);
    }

    public function consume(Branch $branch, Product $product, $documentQty, $uomId, string $movementType, $movementId, $businessDate = null): void
    {
        $stockQty = $this->stockQty($product, $documentQty, $uomId);
        $this->consumeStock($branch, $product, $stockQty, $movementType, $movementId, $businessDate);
    }

    public function consumeStock(Branch $branch, Product $product, float $stockQty, string $movementType, $movementId, $businessDate = null): void
    {
        if ($stockQty <= 0) {
            return;
        }

        $businessDate = Carbon::parse($businessDate ?: now())->toDateString();

        DB::transaction(function () use ($branch, $product, $stockQty, $movementType, $movementId, $businessDate) {
            $remaining = $stockQty;
            $layers = InventoryCostLayer::where('branch_id', $branch->id)
                ->where('product_id', $product->id)
                ->where('qty_remaining', '>', 0)
                ->orderBy('received_at')
                ->orderBy('id')
                ->lockForUpdate()
                ->get();

            foreach ($layers as $layer) {
                if ($remaining <= 0.0000001) {
                    break;
                }

                $take = min($remaining, (float) $layer->qty_remaining);
                $layer->qty_remaining = (float) $layer->qty_remaining - $take;
                $layer->save();

                InventoryCostConsumption::create([
                    'inventory_cost_layer_id' => $layer->id,
                    'qty' => $take,
                    'unit_cost' => $layer->unit_cost,
                    'movement_type' => $movementType,
                    'movement_id' => $movementId,
                    'business_date' => $businessDate,
                ]);

                $remaining -= $take;
            }

            if ($remaining > 0.0000001) {
                $cost = $this->fallbackCost($branch, $product);
                $layer = InventoryCostLayer::create([
                    'branch_id' => $branch->id,
                    'product_id' => $product->id,
                    'source_type' => $movementType,
                    'source_id' => $movementId,
                    'qty_received' => $remaining,
                    'qty_remaining' => 0,
                    'unit_cost' => $cost,
                    'received_at' => now(),
                ]);

                InventoryCostConsumption::create([
                    'inventory_cost_layer_id' => $layer->id,
                    'qty' => $remaining,
                    'unit_cost' => $cost,
                    'movement_type' => $movementType,
                    'movement_id' => $movementId,
                    'business_date' => $businessDate,
                ]);
            }

            $this->syncBranchCost($branch, $product);
        });
    }

    public function applyPhysicalCount(Branch $branch, Product $product, $documentQty, $uomId, $countId, $businessDate = null): void
    {
        $counted = $this->stockQty($product, $documentQty, $uomId);
        $onHand = (float) InventoryCostLayer::where('branch_id', $branch->id)
            ->where('product_id', $product->id)
            ->sum('qty_remaining');

        $difference = $counted - $onHand;
        if ($difference > 0.0000001) {
            $this->receive(
                $branch,
                $product,
                $difference,
                $this->oldestUnitCost($branch, $product),
                'product_physical_counts',
                $countId
            );
        } elseif ($difference < -0.0000001) {
            $this->consumeStock($branch, $product, abs($difference), 'product_physical_counts', $countId, $businessDate);
        }
    }

    public function receiveFromMovement(Branch $branch, Product $product, $documentQty, $uomId, string $sourceMovementType, $sourceMovementId, string $sourceType, $sourceId): void
    {
        $stockQty = $this->stockQty($product, $documentQty, $uomId);
        if ($stockQty <= 0) {
            return;
        }

        $slices = InventoryCostConsumption::query()
            ->select('inventory_cost_consumptions.*')
            ->join('inventory_cost_layers', 'inventory_cost_layers.id', '=', 'inventory_cost_consumptions.inventory_cost_layer_id')
            ->where('inventory_cost_consumptions.movement_type', $sourceMovementType)
            ->where('inventory_cost_consumptions.movement_id', $sourceMovementId)
            ->where('inventory_cost_layers.product_id', $product->id)
            ->orderBy('inventory_cost_consumptions.id')
            ->get();

        $left = $stockQty;
        foreach ($slices as $slice) {
            if ($left <= 0.0000001) {
                break;
            }

            $take = min($left, (float) $slice->qty);
            $this->receive($branch, $product, $take, (float) $slice->unit_cost, $sourceType, $sourceId);
            $left -= $take;
        }

        if ($left > 0.0000001) {
            $cost = $slices->isNotEmpty() ? (float) $slices->last()->unit_cost : $this->fallbackCost($branch, $product);
            $this->receive($branch, $product, $left, $cost, $sourceType, $sourceId);
        }
    }

    public function syncBranchCost(Branch $branch, Product $product): void
    {
        $oldest = InventoryCostLayer::where('branch_id', $branch->id)
            ->where('product_id', $product->id)
            ->where('qty_remaining', '>', 0)
            ->orderBy('received_at')
            ->orderBy('id')
            ->first();

        if (!$oldest) {
            return;
        }

        if ($branch->products()->where('product_id', $product->id)->exists()) {
            $branch->products()->updateExistingPivot($product->id, [
                'cost' => $oldest->unit_cost,
            ]);
        }
    }

    public function unitCostExpression(int $branchId, string $startDate, string $endDate): string
    {
        [$branchId, $start, $end] = $this->reportBounds($branchId, $startDate, $endDate);

        return "COALESCE((
            SELECT SUM(c.qty * c.unit_cost) / NULLIF(SUM(c.qty), 0)
            FROM inventory_cost_consumptions c
            INNER JOIN inventory_cost_layers l ON l.id = c.inventory_cost_layer_id
            WHERE l.product_id = products.id
                AND l.branch_id = {$branchId}
                AND c.movement_type = 'end_of_days'
                AND c.business_date BETWEEN '{$start}' AND '{$end}'
        ), products.cost)";
    }

    public function totalCostExpression(int $branchId, string $startDate, string $endDate, string $qtyColumn = 'transactional_db.orders.qty'): string
    {
        [$branchId, $start, $end] = $this->reportBounds($branchId, $startDate, $endDate);

        return "COALESCE((
            SELECT SUM(c.qty * c.unit_cost)
            FROM inventory_cost_consumptions c
            INNER JOIN inventory_cost_layers l ON l.id = c.inventory_cost_layer_id
            WHERE l.product_id = products.id
                AND l.branch_id = {$branchId}
                AND c.movement_type = 'end_of_days'
                AND c.business_date BETWEEN '{$start}' AND '{$end}'
        ), SUM({$qtyColumn} * products.cost))";
    }

    public function endOfDayCostsByDate(int $branchId, string $startDate, string $endDate): array
    {
        [$branchId, $start, $end] = $this->reportBounds($branchId, $startDate, $endDate);

        $rows = DB::table('inventory_cost_consumptions as c')
            ->join('inventory_cost_layers as l', 'l.id', '=', 'c.inventory_cost_layer_id')
            ->where('l.branch_id', $branchId)
            ->where('c.movement_type', 'end_of_days')
            ->whereBetween('c.business_date', [$start, $end])
            ->groupBy('c.business_date')
            ->selectRaw('c.business_date, SUM(c.qty * c.unit_cost) as cost')
            ->get();

        $costs = [];
        foreach ($rows as $row) {
            $costs[Carbon::parse($row->business_date)->toDateString()] = (float) $row->cost;
        }

        return $costs;
    }

    public function endOfDayCostsByMonth(int $branchId, string $startDate, string $endDate): array
    {
        [$branchId, $start, $end] = $this->reportBounds($branchId, $startDate, $endDate);

        $rows = DB::table('inventory_cost_consumptions as c')
            ->join('inventory_cost_layers as l', 'l.id', '=', 'c.inventory_cost_layer_id')
            ->where('l.branch_id', $branchId)
            ->where('c.movement_type', 'end_of_days')
            ->whereBetween('c.business_date', [$start, $end])
            ->groupBy(DB::raw('YEAR(c.business_date)'), DB::raw('MONTH(c.business_date)'))
            ->selectRaw('YEAR(c.business_date) as year, MONTH(c.business_date) as month, SUM(c.qty * c.unit_cost) as cost')
            ->get();

        $costs = [];
        foreach ($rows as $row) {
            $costs[sprintf('%d-%02d', $row->year, $row->month)] = (float) $row->cost;
        }

        return $costs;
    }

    public function applySummaryCost(object $summary, ?float $fifoCost): void
    {
        if ($fifoCost === null) {
            return;
        }

        $summary->unit_cost = $fifoCost;
        $summary->gross_profit = (float) $summary->net_sales - $fifoCost;
        $summary->gross_profit_percentage = (float) $summary->net_sales > 0
            ? ($summary->gross_profit / (float) $summary->net_sales) * 100
            : 0;
    }

    public function allocateSaleLines(int $branchId, int $productId, string $startDate, string $endDate, array $rows): array
    {
        [$branchId, $start, $end] = $this->reportBounds($branchId, $startDate, $endDate);

        $slices = DB::table('inventory_cost_consumptions as c')
            ->join('inventory_cost_layers as l', 'l.id', '=', 'c.inventory_cost_layer_id')
            ->where('l.branch_id', $branchId)
            ->where('l.product_id', $productId)
            ->where('c.movement_type', 'end_of_days')
            ->whereBetween('c.business_date', [$start, $end])
            ->orderBy('c.id')
            ->get(['c.business_date', 'c.qty', 'c.unit_cost']);

        $queues = [];
        foreach ($slices as $slice) {
            $date = Carbon::parse($slice->business_date)->toDateString();
            $queues[$date][] = [
                'qty' => (float) $slice->qty,
                'unit_cost' => (float) $slice->unit_cost,
            ];
        }

        usort($rows, function ($left, $right) {
            return strcmp((string) $left->transaction_date, (string) $right->transaction_date);
        });

        foreach ($rows as $row) {
            $date = Carbon::parse($row->transaction_date)->toDateString();
            if (empty($queues[$date])) {
                continue;
            }

            $need = (float) $row->qty;
            $value = 0;
            $allocated = 0;

            while ($need > 0.0000001 && !empty($queues[$date])) {
                $take = min($need, $queues[$date][0]['qty']);
                $value += $take * $queues[$date][0]['unit_cost'];
                $allocated += $take;
                $queues[$date][0]['qty'] -= $take;
                $need -= $take;

                if ($queues[$date][0]['qty'] <= 0.0000001) {
                    array_shift($queues[$date]);
                }
            }

            if ($allocated <= 0) {
                continue;
            }

            if ($need > 0.0000001) {
                $value += $need * (float) $row->cost;
            }

            $row->cost = (float) $row->qty > 0 ? $value / (float) $row->qty : $row->cost;
            $row->profit = (float) $row->gross - $value;
        }

        return $rows;
    }

    private function oldestUnitCost(Branch $branch, Product $product): float
    {
        $oldest = InventoryCostLayer::where('branch_id', $branch->id)
            ->where('product_id', $product->id)
            ->where('qty_remaining', '>', 0)
            ->orderBy('received_at')
            ->orderBy('id')
            ->first();

        return $oldest ? (float) $oldest->unit_cost : $this->fallbackCost($branch, $product);
    }

    private function fallbackCost(Branch $branch, Product $product): float
    {
        $pivot = $product->branches()->where('branches.id', $branch->id)->first()?->pivot;
        if ($pivot && (float) $pivot->cost > 0) {
            return (float) $pivot->cost;
        }

        $latest = InventoryCostLayer::where('branch_id', $branch->id)
            ->where('product_id', $product->id)
            ->orderByDesc('id')
            ->value('unit_cost');

        if ($latest) {
            return (float) $latest;
        }

        return (float) ($product->cost ?? 0);
    }

    private function reportBounds(int $branchId, string $startDate, string $endDate): array
    {
        return [
            (int) $branchId,
            Carbon::parse($startDate)->toDateString(),
            Carbon::parse($endDate)->toDateString(),
        ];
    }
}
