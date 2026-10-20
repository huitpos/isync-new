<?php

namespace App\Services;

use App\Models\DebitMemo;
use App\Models\DebitMemoItem;
use App\Models\PurchaseDelivery;
use App\Models\PurchaseDeliveryItem;
use App\Models\PurchaseOrder;
use App\Models\PurchaseReturnItem;
use App\Models\SupplierInvoice;
use App\Models\SupplierInvoiceAllocation;
use App\Models\SupplierInvoiceItem;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ThreeWayMatchService
{
    public function purchaseOrderSummary(PurchaseOrder $po): array
    {
        $itemIds = $po->items->pluck('id')->all();
        $received = $this->sumDeliveryQty($itemIds);
        $legacy = $this->sumDeliveryQty($itemIds, true);
        $invoiced = $this->sumAllocatedQty($itemIds);
        $returned = $this->sumReturnQty($itemIds);

        $summary = [];
        foreach ($po->items as $item) {
            $receivedQty = $this->round4($received[$item->id] ?? 0);
            $billedQty = $this->round4(($legacy[$item->id] ?? 0) + ($invoiced[$item->id] ?? 0));
            $returnedQty = $this->round4($returned[$item->id] ?? 0);
            $unbilled = max(0, $this->round4($receivedQty - $billedQty));
            $summary[$item->id] = [
                'ordered' => $this->round4($item->quantity),
                'received' => $receivedQty,
                'billed' => $billedQty,
                'returned' => $returnedQty,
                'open' => max(0, $this->round4($receivedQty - $billedQty - $returnedQty)),
                'return_to_debit' => max(0, $this->round4($returnedQty - $unbilled)),
            ];
        }

        return $summary;
    }

    public function openPurchaseOrders(int $branchId): Collection
    {
        return PurchaseOrder::with(['items.product', 'items.uom', 'supplier'])
            ->where('branch_id', $branchId)
            ->where('status', 'approved')
            ->get()
            ->filter(function (PurchaseOrder $po) {
                return collect($this->purchaseOrderSummary($po))->contains(fn ($line) => $line['open'] > 0);
            })
            ->values();
    }

    public function deliveryBillingStatus(PurchaseDelivery $delivery): string
    {
        if ($delivery->is_legacy_billed) {
            return 'billed';
        }

        $received = $this->round4($delivery->items->sum('qty'));
        if ($delivery->status !== 'approved' || $received <= 0) {
            return 'unbilled';
        }

        $billed = $this->round4(
            SupplierInvoiceAllocation::query()
                ->whereIn('purchase_delivery_item_id', $delivery->items->pluck('id'))
                ->whereHas('invoiceItem.supplierInvoice', function ($query) {
                    $query->where('status', '!=', 'void');
                })
                ->sum('qty')
        );

        if ($billed <= 0) {
            return 'unbilled';
        }

        if ($billed + 0.00005 < $received) {
            return 'partially billed';
        }

        return 'billed';
    }

    public function create(PurchaseOrder $po, array $header, array $lines): SupplierInvoice
    {
        if ($po->status !== 'approved') {
            throw ValidationException::withMessages([
                'purchase_order_id' => 'Only an approved purchase order can be billed.',
            ]);
        }

        return DB::transaction(function () use ($po, $header, $lines) {
            PurchaseOrder::where('id', $po->id)->lockForUpdate()->first();
            $po->load('items.product', 'items.uom');
            $summary = $this->purchaseOrderSummary($po);

            $invoice = SupplierInvoice::create([
                'branch_id' => $po->branch_id,
                'supplier_id' => $po->supplier_id,
                'purchase_order_id' => $po->id,
                'invoice_number' => $header['invoice_number'],
                'invoice_date' => $header['invoice_date'],
                'total_amount' => 0,
                'status' => 'draft',
            ]);

            $total = 0;
            $hasVariance = false;

            foreach ($lines as $line) {
                $poItem = $po->items->firstWhere('id', (int) $line['purchase_order_item_id']);
                if (!$poItem) {
                    throw ValidationException::withMessages([
                        'items' => 'An item does not belong to this purchase order.',
                    ]);
                }

                $qty = $this->round4($line['qty']);
                $unitPrice = $this->round4($line['unit_price']);
                $poPrice = $this->round4($poItem->unit_price);
                $open = $summary[$poItem->id]['open'] ?? 0;
                $varianceType = $this->varianceType($qty, $open, $unitPrice, $poPrice);
                if ($varianceType) {
                    $hasVariance = true;
                }

                $lineAmount = $this->round4($qty * $unitPrice);
                $total = $this->round4($total + $lineAmount);

                $invoiceItem = $invoice->items()->create([
                    'purchase_order_item_id' => $poItem->id,
                    'product_id' => $poItem->product_id,
                    'uom_id' => $poItem->uom_id,
                    'qty' => $qty,
                    'unit_price' => $unitPrice,
                    'po_unit_price' => $poPrice,
                    'line_amount' => $lineAmount,
                    'variance_type' => $varianceType,
                ]);

                $this->allocate($invoiceItem, $poItem->id, min($qty, $open));
            }

            $invoice->update([
                'total_amount' => $total,
                'status' => $hasVariance ? 'variance' : 'matched',
            ]);

            return $invoice->fresh('items');
        });
    }

    public function accept(SupplierInvoice $invoice, array $reasons, int $userId): SupplierInvoice
    {
        if (!in_array($invoice->status, ['matched', 'variance'], true)) {
            throw ValidationException::withMessages([
                'status' => 'This invoice can no longer be accepted.',
            ]);
        }

        return DB::transaction(function () use ($invoice, $reasons, $userId) {
            $invoice->load('items');

            if ($invoice->status === 'variance') {
                $errors = [];
                foreach ($invoice->items as $item) {
                    if (!$item->variance_type) {
                        continue;
                    }
                    $reason = trim((string) ($reasons[$item->id] ?? ''));
                    if ($reason === '') {
                        $errors["variance_reasons.{$item->id}"] = 'A reason is required to accept this variance.';
                    }
                }
                if ($errors) {
                    throw ValidationException::withMessages($errors);
                }

                foreach ($invoice->items as $item) {
                    if (!$item->variance_type) {
                        continue;
                    }
                    $item->variance_reason = trim((string) $reasons[$item->id]);
                    $item->variance_approved_by = $userId;
                    $item->save();
                }
            }

            $invoice->status = 'accepted';
            $invoice->accepted_by = $userId;
            $invoice->accepted_at = now();
            $invoice->save();

            app(\App\Services\Gl\DocumentJournalService::class)->postSupplierInvoice($invoice);

            return $invoice;
        });
    }

    public function void(SupplierInvoice $invoice, int $userId): SupplierInvoice
    {
        if (!in_array($invoice->status, ['matched', 'variance', 'accepted'], true)) {
            throw ValidationException::withMessages([
                'status' => 'This invoice can no longer be voided.',
            ]);
        }

        if (DebitMemo::where('supplier_invoice_id', $invoice->id)->where('status', '!=', 'void')->exists()) {
            throw ValidationException::withMessages([
                'status' => 'Void the debit memo before voiding this invoice.',
            ]);
        }

        return DB::transaction(function () use ($invoice, $userId) {
            $wasAccepted = $invoice->status === 'accepted';
            $invoice->loadMissing('branch');

            $invoice->status = 'void';
            $invoice->voided_by = $userId;
            $invoice->voided_at = now();
            $invoice->save();

            if ($wasAccepted) {
                app(\App\Services\Gl\DocumentJournalService::class)->voidIfPosted(
                    (int) $invoice->branch->company_id,
                    'supplier_invoice',
                    (string) $invoice->id
                );
            }

            return $invoice;
        });
    }

    public function returnDebitOpen(PurchaseOrder $po): array
    {
        $summary = $this->purchaseOrderSummary($po);
        $returnItems = PurchaseReturnItem::with('purchaseReturn')
            ->whereHas('purchaseReturn', function ($query) use ($po) {
                $query->where('purchase_order_id', $po->id)->where('status', 'approved');
            })
            ->get()
            ->sortBy(function (PurchaseReturnItem $item) {
                return $item->purchaseReturn->created_at->format('Y-m-d H:i:s') . '-' . str_pad((string) $item->id, 10, '0', STR_PAD_LEFT);
            })
            ->groupBy('purchase_order_item_id');

        $debited = $this->sumDebitedReturnQty($returnItems->flatten()->pluck('id')->all());
        $lines = [];

        foreach ($returnItems as $poItemId => $items) {
            $received = $summary[$poItemId]['received'] ?? 0;
            $billed = $summary[$poItemId]['billed'] ?? 0;
            $unbilledPool = max(0, $this->round4($received - $billed));

            foreach ($items as $item) {
                $qty = $this->round4($item->qty);
                $unbilledPart = min($qty, $unbilledPool);
                $unbilledPool = $this->round4($unbilledPool - $unbilledPart);
                $eligible = $this->round4($qty - $unbilledPart);
                $used = $this->round4($debited[$item->id] ?? 0);
                $lines[$item->id] = [
                    'purchase_return_item_id' => $item->id,
                    'purchase_order_item_id' => (int) $poItemId,
                    'return_number' => $item->purchaseReturn->return_number,
                    'unit_price' => $this->round4($item->unit_price),
                    'eligible' => $eligible,
                    'debited' => $used,
                    'open' => max(0, $this->round4($eligible - $used)),
                ];
            }
        }

        return $lines;
    }

    public function invoiceDebitRemaining(SupplierInvoice $invoice): array
    {
        $invoice->loadMissing('items');
        $debited = $this->sumDebitedInvoiceAmounts($invoice->items->pluck('id')->all());
        $remaining = [];

        foreach ($invoice->items as $item) {
            $usedAmount = $this->round4($debited[$item->id]['amount'] ?? 0);
            $usedQty = $this->round4($debited[$item->id]['qty'] ?? 0);
            $remaining[$item->id] = [
                'amount' => max(0, $this->round4($item->line_amount - $usedAmount)),
                'qty' => max(0, $this->round4($item->qty - $usedQty)),
            ];
        }

        return $remaining;
    }

    private function sumReturnQty(array $itemIds): array
    {
        if (!$itemIds) {
            return [];
        }

        return PurchaseReturnItem::query()
            ->selectRaw('purchase_order_item_id, SUM(qty) as qty')
            ->whereIn('purchase_order_item_id', $itemIds)
            ->whereHas('purchaseReturn', function ($query) {
                $query->where('status', 'approved');
            })
            ->groupBy('purchase_order_item_id')
            ->pluck('qty', 'purchase_order_item_id')
            ->all();
    }

    private function sumDebitedReturnQty(array $returnItemIds): array
    {
        if (!$returnItemIds) {
            return [];
        }

        return DebitMemoItem::query()
            ->selectRaw('purchase_return_item_id, SUM(qty) as qty')
            ->whereIn('purchase_return_item_id', $returnItemIds)
            ->whereHas('debitMemo', function ($query) {
                $query->where('status', '!=', 'void');
            })
            ->groupBy('purchase_return_item_id')
            ->pluck('qty', 'purchase_return_item_id')
            ->all();
    }

    private function sumDebitedInvoiceAmounts(array $invoiceItemIds): array
    {
        if (!$invoiceItemIds) {
            return [];
        }

        return DebitMemoItem::query()
            ->selectRaw('supplier_invoice_item_id, SUM(qty) as qty, SUM(line_amount) as amount')
            ->whereIn('supplier_invoice_item_id', $invoiceItemIds)
            ->whereHas('debitMemo', function ($query) {
                $query->where('status', '!=', 'void');
            })
            ->groupBy('supplier_invoice_item_id')
            ->get()
            ->mapWithKeys(function ($row) {
                return [$row->supplier_invoice_item_id => [
                    'qty' => $row->qty,
                    'amount' => $row->amount,
                ]];
            })
            ->all();
    }

    private function allocate(SupplierInvoiceItem $invoiceItem, int $purchaseOrderItemId, float $qty): void
    {
        $remaining = $this->round4($qty);
        if ($remaining <= 0) {
            return;
        }

        $deliveryItems = PurchaseDeliveryItem::query()
            ->select('purchase_delivery_items.*')
            ->join('purchase_deliveries', 'purchase_deliveries.id', '=', 'purchase_delivery_items.purchase_delivery_id')
            ->where('purchase_delivery_items.purchase_order_item_id', $purchaseOrderItemId)
            ->where('purchase_deliveries.status', 'approved')
            ->where('purchase_deliveries.is_legacy_billed', false)
            ->orderBy('purchase_deliveries.created_at')
            ->orderBy('purchase_delivery_items.id')
            ->get();

        $allocated = SupplierInvoiceAllocation::query()
            ->selectRaw('purchase_delivery_item_id, SUM(qty) as qty')
            ->whereIn('purchase_delivery_item_id', $deliveryItems->pluck('id'))
            ->whereHas('invoiceItem.supplierInvoice', function ($query) {
                $query->where('status', '!=', 'void');
            })
            ->groupBy('purchase_delivery_item_id')
            ->pluck('qty', 'purchase_delivery_item_id');

        foreach ($deliveryItems as $deliveryItem) {
            if ($remaining <= 0) {
                break;
            }

            $available = $this->round4($deliveryItem->qty - ($allocated[$deliveryItem->id] ?? 0));
            if ($available <= 0) {
                continue;
            }

            $take = min($remaining, $available);
            $invoiceItem->allocations()->create([
                'purchase_delivery_item_id' => $deliveryItem->id,
                'qty' => $take,
            ]);
            $remaining = $this->round4($remaining - $take);
        }
    }

    private function varianceType(float $qty, float $open, float $unitPrice, float $poPrice): ?string
    {
        $qtyVariance = $qty > $open;
        $priceVariance = $unitPrice !== $poPrice;

        if ($qtyVariance && $priceVariance) {
            return 'both';
        }
        if ($qtyVariance) {
            return 'qty';
        }
        if ($priceVariance) {
            return 'price';
        }

        return null;
    }

    private function sumDeliveryQty(array $itemIds, ?bool $legacy = null): array
    {
        if (!$itemIds) {
            return [];
        }

        return PurchaseDeliveryItem::query()
            ->selectRaw('purchase_order_item_id, SUM(qty) as qty')
            ->whereIn('purchase_order_item_id', $itemIds)
            ->whereHas('purchaseDelivery', function ($query) use ($legacy) {
                $query->where('status', 'approved');
                if ($legacy !== null) {
                    $query->where('is_legacy_billed', $legacy);
                }
            })
            ->groupBy('purchase_order_item_id')
            ->pluck('qty', 'purchase_order_item_id')
            ->all();
    }

    private function sumAllocatedQty(array $itemIds): array
    {
        if (!$itemIds) {
            return [];
        }

        return SupplierInvoiceItem::query()
            ->selectRaw('supplier_invoice_items.purchase_order_item_id, SUM(supplier_invoice_allocations.qty) as qty')
            ->join('supplier_invoice_allocations', 'supplier_invoice_allocations.supplier_invoice_item_id', '=', 'supplier_invoice_items.id')
            ->join('supplier_invoices', 'supplier_invoices.id', '=', 'supplier_invoice_items.supplier_invoice_id')
            ->whereIn('supplier_invoice_items.purchase_order_item_id', $itemIds)
            ->where('supplier_invoices.status', '!=', 'void')
            ->groupBy('supplier_invoice_items.purchase_order_item_id')
            ->pluck('qty', 'purchase_order_item_id')
            ->all();
    }

    private function round4($value): float
    {
        return round((float) $value, 4);
    }
}
