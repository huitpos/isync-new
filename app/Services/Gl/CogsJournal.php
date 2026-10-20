<?php

namespace App\Services\Gl;

use App\Models\Branch;
use App\Models\InventoryCostConsumption;
use App\Models\JournalEntry;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CogsJournal
{
    public function __construct(
        private AccountCatalog $catalog,
        private JournalPoster $poster
    ) {
    }

    public function sourceKey(int $branchId, string $date): string
    {
        return $branchId.':'.$date;
    }

    public function existing(int $companyId, int $branchId, string $date): ?JournalEntry
    {
        return JournalEntry::where('company_id', $companyId)
            ->where('source_type', 'cogs')
            ->where('source_key', $this->sourceKey($branchId, $date))
            ->where('status', 'posted')
            ->first();
    }

    public function amount(int $branchId, string $date): float
    {
        $total = InventoryCostConsumption::query()
            ->join('inventory_cost_layers', 'inventory_cost_layers.id', '=', 'inventory_cost_consumptions.inventory_cost_layer_id')
            ->where('inventory_cost_layers.branch_id', $branchId)
            ->where('inventory_cost_consumptions.movement_type', 'end_of_days')
            ->whereDate('inventory_cost_consumptions.business_date', $date)
            ->sum(DB::raw('inventory_cost_consumptions.qty * inventory_cost_consumptions.unit_cost'));

        return round((float) $total, 4);
    }

    public function post(Branch $branch, string $date): JournalEntry
    {
        $companyId = (int) $branch->company_id;
        $amount = $this->amount($branch->id, $date);

        if ($amount <= 0) {
            throw ValidationException::withMessages([
                'date' => 'No end-of-day cost consumption for this date.',
            ]);
        }

        return $this->poster->post($companyId, [
            'entry_date' => $date,
            'source_type' => 'cogs',
            'source_key' => $this->sourceKey($branch->id, $date),
            'description' => 'COGS '.$branch->name.' '.$date,
        ], [
            [
                'account_id' => $this->catalog->id($companyId, 'cogs'),
                'branch_id' => $branch->id,
                'debit' => $amount,
                'credit' => 0,
                'memo' => 'End of day FIFO',
            ],
            [
                'account_id' => $this->catalog->id($companyId, 'inventory'),
                'branch_id' => $branch->id,
                'debit' => 0,
                'credit' => $amount,
                'memo' => 'End of day FIFO',
            ],
        ]);
    }
}
