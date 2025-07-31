<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Branch;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\ProductDisposal;
use App\Models\ProductPhysicalCount;
use Illuminate\Support\Facades\DB;

use App\Repositories\Interfaces\ProductRepositoryInterface;

class TestController extends Controller
{
    protected $productRepository;

    public function __construct(
        ProductRepositoryInterface $productRepository
    ) {
        $this->productRepository = $productRepository;
    }

    public function mapData(Request $request)
    {
        $transactionalDbName = config('database.connections.transactional_db.database');

        $branchId = $request->input('branch_id');
        $productId = $request->input('product_id');
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

        foreach ($products as $product) {
            $_product = Product::find($product->product_id);
            $branch = Branch::find($product->branch_id);

            // Check for duplicate end_of_days logs
            $duplicateLogs = DB::select('
                SELECT object_id, COUNT(*) as count
                FROM product_count_logs
                WHERE branch_id = ?
                AND product_id = ?
                AND object_type = "end_of_days"
                GROUP BY object_id
                HAVING count > 1
            ', [$product->branch_id, $product->product_id]);

            // Skip if no duplicates found
            if (empty($duplicateLogs)) {
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
            $incomingQuery = 'SELECT SUM(purchase_delivery_items.qty) as total
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

            $transactionQuery = "
                SELECT
                    sum(orders.qty) as total
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

            $soh = $baseQty + $incomingTotal - $transactionTotal;

            // Only update if the current stock doesn't match the calculated SOH
            if ($product->stock != $soh) {
                $this->productRepository->updateBranchQuantity($_product, $branch, 0, 'manual_edit', $soh, null, 'replace', $_product->uom_id);
            }
        }
    }

    public function fixGsmarine()
    {
        $branch = Branch::where('id', 19)->first();
        $branch = Branch::findOrFail(19);

        $transactions = Transaction::where('branch_id', $branch->id)
            ->where('is_void', false)
            ->where('is_back_out', false)
            ->where('is_complete', true)
            ->where('cut_off_id', 212)
            ->get();

        foreach ($transactions as $transaction) {
            $orders = $transaction->nonVoiditems()
                ->where('is_void', false)
                ->where('is_completed', true)
                ->where('is_back_out', false)
                ->get();

            foreach ($orders as $order) {
                $product = Product::find($order->product_id);

                if ($product) {
                    $this->productRepository->updateBranchQuantity(
                        $product,
                        $branch,
                        0,
                        'manual_edit',
                        $order->qty,
                        null,
                        'subtract',
                        $product->uom_id
                    );
                }
            }
        }

        dd("Branch {$branch->name} transactions have been processed successfully.");
    }
}