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
        // $products = DB::select('SELECT * FROM branch_product where stock != 0');
        $products = DB::select('SELECT * FROM branch_product where branch_id = 19 and product_id = 8923');

        foreach ($products as $product) {
            $_product = Product::find($product->product_id);
            $branch = Branch::find($product->branch_id);

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

            $this->productRepository->updateBranchQuantity($_product, $branch, 0, 'manual_edit', $soh, null, 'replace', $_product->uom_id);
        }
    }
}