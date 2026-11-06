<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CopyBranchData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:copy-branch-data {from_branch_id} {to_branch_id} {--copy-stock=no}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Copy branch_product data from one branch to another';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $fromBranchId = $this->argument('from_branch_id');
        $toBranchId = $this->argument('to_branch_id');
        $copyStock = strtolower($this->option('copy-stock')) === 'yes';

        if ($fromBranchId == $toBranchId) {
            $this->error('From and to branch IDs must be different.');
            return 1;
        }

        $this->info("Fetching existing products for branch {$toBranchId}...");
        $existingByProductId = DB::table('branch_product')
            ->where('branch_id', $toBranchId)
            ->get()
            ->keyBy('product_id');
        $this->info("Found {$existingByProductId->count()} existing products.");

        $this->info("Fetching products from branch {$fromBranchId}...");
        $branchProducts = DB::table('branch_product')
            ->where('branch_id', $fromBranchId)
            ->get();
        $this->info("Found {$branchProducts->count()} products to process.");
        $this->info('Stock will be ' . ($copyStock ? 'copied.' : 'set to 0.'));

        if ($branchProducts->isEmpty()) {
            $this->warn('No products found to copy.');
            return 0;
        }

        $copied = 0;
        $updated = 0;
        $now = now();
        $bar = $this->output->createProgressBar($branchProducts->count());
        $bar->start();

        foreach ($branchProducts as $branchProduct) {
            $stock = $copyStock ? $branchProduct->stock : 0;
            $existing = $existingByProductId->get($branchProduct->product_id);

            $data = [
                'price' => $branchProduct->price,
                'cost' => $branchProduct->cost,
                'markup' => $branchProduct->markup,
                'show_to_branch' => $branchProduct->show_to_branch,
                'stock' => $stock,
                'updated_at' => $now,
            ];

            if ($existing) {
                DB::table('branch_product')
                    ->where('id', $existing->id)
                    ->update($data);
                $updated++;
            } else {
                DB::table('branch_product')->insert(array_merge($data, [
                    'branch_id' => $toBranchId,
                    'product_id' => $branchProduct->product_id,
                    'created_at' => $now,
                ]));
                $copied++;
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);
        $this->info("Done. Copied {$copied} and updated {$updated} branch products from branch {$fromBranchId} to branch {$toBranchId}.");

        return 0;
    }
}
