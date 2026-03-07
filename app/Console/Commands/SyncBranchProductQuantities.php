<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Branch;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use App\Repositories\Interfaces\ProductRepositoryInterface;

class SyncBranchProductQuantities extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'products:sync-quantities 
                            {--branch-id= : Sync for specific branch only}
                            {--product-id= : Sync for specific product only}
                            {--chunk=100 : Process records in chunks of this size}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync branch product quantities based on PPC, incoming purchases, and transactions (optimized for performance)';

    protected $productRepository;

    public function __construct(ProductRepositoryInterface $productRepository)
    {
        parent::__construct();
        $this->productRepository = $productRepository;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting branch product quantity sync...');

        $transactionalDbName = config('database.connections.transactional_db.database');
        $chunkSize = (int)$this->option('chunk');

        // Build query with optional filters
        $query = DB::table('branch_product')
            ->select(['id', 'branch_id', 'product_id', 'stock']);

        if ($this->option('branch-id')) {
            $query->where('branch_id', $this->option('branch-id'));
            $this->info('Filtering by branch ID: ' . $this->option('branch-id'));
        }

        if ($this->option('product-id')) {
            $query->where('product_id', $this->option('product-id'));
            $this->info('Filtering by product ID: ' . $this->option('product-id'));
        }

        $total = $query->count();

        if ($total === 0) {
            $this->warn('No records found to process.');
            return;
        }

        $this->info("Found {$total} records to process.");

        $progressBar = $this->output->createProgressBar($total);
        $progressBar->start();

        $updated = 0;
        $skipped = 0;

        // Process in chunks
        $query->chunk($chunkSize, function ($branchProducts) use (
            &$updated,
            &$skipped,
            $progressBar,
            $transactionalDbName
        ) {
            foreach ($branchProducts as $record) {
                try {
                    $product = Product::find($record->product_id);
                    $branch = Branch::find($record->branch_id);

                    if (!$product || !$branch) {
                        $skipped++;
                        $progressBar->advance();
                        continue;
                    }

                    // Get latest PPC
                    $latestPPC = DB::table('product_count_logs')
                        ->where('object_type', 'product_physical_counts')
                        ->where('branch_id', $record->branch_id)
                        ->where('product_id', $record->product_id)
                        ->orderByDesc('created_at')
                        ->first();

                    $baseQty = 0;
                    $baseDate = null;
                    if ($latestPPC) {
                        $baseQty = $latestPPC->new_quantity;
                        $baseDate = $latestPPC->created_at;
                    }

                    // Get incoming after the latest PPC
                    $incomingQuery = DB::table('purchase_delivery_items')
                        ->join(
                            'purchase_deliveries',
                            'purchase_delivery_items.purchase_delivery_id',
                            '=',
                            'purchase_deliveries.id'
                        )
                        ->where('purchase_delivery_items.product_id', $record->product_id)
                        ->where('purchase_deliveries.branch_id', $record->branch_id)
                        ->where('purchase_deliveries.status', 'approved');

                    if ($baseDate) {
                        $incomingQuery->where('purchase_deliveries.created_at', '>', $baseDate);
                    }

                    $incomingTotal = $incomingQuery->sum('purchase_delivery_items.qty') ?? 0;

                    // Get transaction total
                    $transactionQuery = "
                        SELECT COALESCE(SUM(orders.qty), 0) as total
                        FROM $transactionalDbName.transactions
                        INNER JOIN $transactionalDbName.orders ON 
                            transactions.transaction_id = orders.transaction_id
                            AND transactions.branch_id = orders.branch_id
                            AND transactions.pos_machine_id = orders.pos_machine_id
                        WHERE transactions.is_complete = TRUE
                            AND transactions.branch_id = ?
                            AND transactions.is_void = FALSE
                            AND transactions.is_back_out = FALSE
                            AND orders.is_void = FALSE
                            AND orders.is_completed = TRUE
                            AND orders.is_back_out = FALSE
                            AND orders.product_id = ?
                    ";

                    $transactions = DB::select($transactionQuery, [
                        $record->branch_id,
                        $record->product_id
                    ]);

                    $transactionTotal = $transactions[0]->total ?? 0;

                    $soh = $baseQty + $incomingTotal - $transactionTotal;

                    // Only update if the current stock doesn't match the calculated SOH
                    if ($record->stock != $soh) {
                        $this->productRepository->updateBranchQuantity(
                            $product,
                            $branch,
                            0,
                            'manual_edit',
                            $soh,
                            null,
                            'replace',
                            $product->uom_id
                        );
                        $updated++;
                    } else {
                        $skipped++;
                    }
                } catch (\Exception $e) {
                    $this->error("Error processing product {$record->product_id} for branch {$record->branch_id}: " . $e->getMessage());
                    $skipped++;
                }

                $progressBar->advance();
            }
        });

        $progressBar->finish();
        $this->newLine(2);

        $this->info("✓ Sync completed!");
        $this->line("  Updated: {$updated}");
        $this->line("  Skipped: {$skipped}");
    }
}
