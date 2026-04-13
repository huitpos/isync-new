<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Branch;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use App\Repositories\Interfaces\ProductRepositoryInterface;


# Map all products
// php artisan product:map-data

# Map for a specific branch
// php artisan product:map-data --branch_id=14

# Map for a specific product
// php artisan product:map-data --product_id=100

# Map for a specific branch AND product
// php artisan product:map-data --branch_id=1 --product_id=100

class MapProductData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'product:map-data {--branch_id=} {--product_id=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Map and update product stock on hand based on physical counts, incoming, and transactions';

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
        $transactionalDbName = config('database.connections.transactional_db.database');

        $branchId = $this->option('branch_id');
        $productId = $this->option('product_id');
        
        $query = 'SELECT * FROM branch_product';

        $conditions = [];
        if ($branchId) {
            $conditions[] = 'branch_id = ' . $branchId;
        }
        if ($productId) {
            $conditions[] = 'product_id = ' . $productId;
        }

        if (!empty($conditions)) {
            $query .= ' WHERE ' . implode(' AND ', $conditions);
        }

        $products = DB::select($query);
        $updateCount = 0;

        foreach ($products as $product) {
            $_product = Product::find($product->product_id);
            $branch = Branch::find($product->branch_id);

            if (!$_product || !$branch) {
                continue;
            }

            $latestPPC = DB::table('product_count_logs')
                ->where('object_type', 'product_physical_counts')
                ->where('branch_id', $product->branch_id)
                ->where('product_id', $product->product_id)
                ->orderByDesc('created_at')
                ->first();

            $baseQty = 0;
            $baseDate = null;
            if ($latestPPC) {
                $baseQty = $latestPPC->new_quantity;
                $baseDate = $latestPPC->created_at;
            }

            // Get incoming after the latest PPC
            $incomingQuery = 'SELECT CAST(SUM(purchase_delivery_items.qty) AS DECIMAL(10,4)) as total
                FROM purchase_deliveries
                INNER JOIN purchase_delivery_items ON purchase_deliveries.id = purchase_delivery_items.purchase_delivery_id
                WHERE purchase_delivery_items.product_id = ?
                AND purchase_deliveries.branch_id = ?
                AND purchase_deliveries.status = ?';
            $incomingParams = [$product->product_id, $product->branch_id, 'approved'];
            if ($baseDate) {
                $incomingQuery .= ' AND purchase_deliveries.created_at > ?';
                $incomingParams[] = $baseDate;
            }
            $incoming = DB::select($incomingQuery, $incomingParams);

            $incomingTotal = $incoming[0]->total ?? 0;

            $incomingTransferQuery = 'SELECT CAST(SUM(stock_transfer_delivery_items.qty) AS DECIMAL(10,4)) as total
                FROM stock_transfer_deliveries
                INNER JOIN stock_transfer_delivery_items ON stock_transfer_deliveries.id = stock_transfer_delivery_items.stock_transfer_delivery_id
                WHERE stock_transfer_delivery_items.product_id = ?
                AND stock_transfer_deliveries.destination_branch_id = ?
                AND stock_transfer_deliveries.status = ?';
            $incomingTransferParams = [$product->product_id, $product->branch_id, 'approved'];
            if ($baseDate) {
                $incomingTransferQuery .= ' AND stock_transfer_deliveries.created_at > ?';
                $incomingTransferParams[] = $baseDate;
            }
            $incomingTransfer = DB::select($incomingTransferQuery, $incomingTransferParams);
            $incomingTransferTotal = $incomingTransfer[0]->total ?? 0;

            $outgoingTransferQuery = 'SELECT CAST(SUM(stock_transfer_order_items.quantity) AS DECIMAL(10,4)) as total
                FROM stock_transfer_orders
                INNER JOIN stock_transfer_order_items ON stock_transfer_orders.id = stock_transfer_order_items.stock_transfer_order_id
                WHERE stock_transfer_order_items.product_id = ?
                AND stock_transfer_orders.source_branch_id = ?
                AND stock_transfer_orders.status = ?';
            $outgoingTransferParams = [$product->product_id, $product->branch_id, 'approved'];
            if ($baseDate) {
                $outgoingTransferQuery .= ' AND stock_transfer_orders.created_at > ?';
                $outgoingTransferParams[] = $baseDate;
            }
            $outgoingTransfer = DB::select($outgoingTransferQuery, $outgoingTransferParams);
            $outgoingTransferTotal = $outgoingTransfer[0]->total ?? 0;

            $transactionQuery = "
                SELECT
                    CAST(SUM(orders.qty) AS DECIMAL(10,4)) as total
                FROM $transactionalDbName.transactions
                INNER JOIN $transactionalDbName.orders ON transactions.transaction_id = orders.transaction_id
                    AND transactions.branch_id = orders.branch_id
                    AND transactions.pos_machine_id = orders.pos_machine_id
                    AND orders.is_void = FALSE
                    AND orders.is_completed = TRUE
                    AND orders.is_back_out = FALSE
                WHERE transactions.is_complete = TRUE
                    AND transactions.branch_id = $product->branch_id
                    AND transactions.is_void = FALSE
                    AND transactions.is_back_out = FALSE
                    AND orders.product_id = $product->product_id
                ";

            $transactions = DB::select($transactionQuery);

            $transactionTotal = $transactions[0]->total ?? 0;

            $soh = $baseQty + $incomingTotal + $incomingTransferTotal - $transactionTotal - $outgoingTransferTotal;

            // Only update if the current stock doesn't match the calculated SOH
            if ($product->stock != $soh) {
                $this->productRepository->updateBranchQuantity($_product, $branch, 0, 'manual_edit', $soh, null, 'replace', $_product->uom_id);
                $updateCount++;
                $this->info("Updated Product ID: $product->product_id | Branch ID: $product->branch_id | Old SOH: $product->stock | New SOH: $soh");
            }
        }

        $this->info("Total products updated: $updateCount");
    }
}
