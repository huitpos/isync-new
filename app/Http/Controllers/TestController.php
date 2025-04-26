<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Branch;
use App\Models\Product;
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

            $incoming = DB::select('SELECT SUM(purchase_delivery_items.qty) `total` FROM purchase_deliveries
                INNER JOIN purchase_delivery_items ON purchase_deliveries.id = purchase_delivery_items.purchase_delivery_id
                WHERE purchase_delivery_items.product_id = ?
                AND purchase_deliveries.branch_id = ?
                AND purchase_deliveries.`status` = ?', [$product->product_id, $product->branch_id, 'approved']);

            $incomingTotal = $incoming[0]->total ?? 0;

            $transactionQuery = "
                SELECT
                    sum(orders.qty) as total
                FROM transactional_db.transactions
                INNER JOIN transactional_db.orders ON transactions.transaction_id = orders.transaction_id
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

            $soh = $incomingTotal - $transactionTotal;

            // Only update if the current stock doesn't match the calculated SOH
            if ($product->stock != $soh) {
                $this->productRepository->updateBranchQuantity($_product, $branch, 0, 'manual_edit', $soh, null, 'replace', $_product->uom_id);
            }
        }
    }
}