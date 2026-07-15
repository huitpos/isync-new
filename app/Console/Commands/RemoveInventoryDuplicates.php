<?php

namespace App\Console\Commands;

use App\Models\InventoryMovementLog;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class RemoveInventoryDuplicates extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    //php artisan products:remove-inventory-duplicates
    protected $signature = 'products:remove-inventory-duplicates';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove inventory duplicates';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting to remove inventory duplicates...');

        $duplicates = DB::connection('inventory')
            ->table('inventory_movement_logs')
            ->select(
                'branch_id',
                'product_id',
                'movement_type',
                'object_id',
                DB::raw('COUNT(*) AS duplicate_count')
            )
            ->groupBy('branch_id', 'product_id', 'movement_type', 'object_id')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        if ($duplicates->isEmpty()) {
            $this->info('No duplicates found.');
            return Command::SUCCESS;
        }

        $this->info("Found {$duplicates->count()} duplicate group(s).");
        $idsToDelete = [];

        foreach ($duplicates as $duplicate) {
            $logs = InventoryMovementLog::where('branch_id', $duplicate->branch_id)
                ->where('product_id', $duplicate->product_id)
                ->where('movement_type', $duplicate->movement_type)
                ->where('object_id', $duplicate->object_id)
                ->orderBy('id')
                ->get();

            $branchProduct = DB::connection('inventory')
                ->table('branch_product')
                ->where('branch_id', $duplicate->branch_id)
                ->where('product_id', $duplicate->product_id)
                ->orderBy('id')
                ->first();

            //exclude the first log
            $logs = $logs->slice(1);
            $currentStock = $branchProduct->stock;
            foreach ($logs as $log) {
                $idsToDelete[] = $log->id;

                $currentStock += $log->previous_qty - $log->new_qty;
            }

            DB::connection('inventory')->table('branch_product')->where('id', $branchProduct->id)->update([
                'stock' => $currentStock,
            ]);

            $removed = InventoryMovementLog::whereIn('id', $idsToDelete)->delete();

            $this->info("Removed {$removed} duplicate log(s).");
        }
    }
}
