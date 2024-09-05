<?php

namespace App\Http\Controllers\Branch;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Transaction;
use App\Models\CutOff;
use App\Models\PaymentType;
use App\Models\DiscountType;
use App\Models\EndOfDay;
use App\Models\Discount;
use App\Models\Branch;
use App\Models\Product;

use Carbon\Carbon;

use App\Exports\SalesTransactionReportExport;
use App\Exports\VoidTransactionsReportExport;
use App\Exports\VatSalesReportExport;
use App\Exports\XReadingReportExport;
use App\Exports\ZReadingReportExport;
use App\Exports\SalesInvoicesReportExport;
use App\Exports\DiscountsReportExport;
use App\Exports\ItemSalesReportExport;

use Maatwebsite\Excel\Facades\Excel;

use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function viewTransaction(Request $request, $companySlug, $branchSlug, $id)
    {
        $company = $request->attributes->get('company');
        $branch = $request->attributes->get('branch');

        $transaction = Transaction::where(['id' => $id])
            ->first();

        return view('branch.reports.viewTransaction', compact('company', 'branch', 'transaction'));
    }

    public function salesInvoicesReport(Request $request)
    {
        $company = $request->attributes->get('company');
        $branch = $request->attributes->get('branch');

        $branchId = $branch->id;

        $dateParam = $request->input('date_range', null);

        $startDate = Carbon::now()->format('Y-m-d 00:00:00');
        $endDate = Carbon::now()->format('Y-m-d 23:59:59');
        if ($dateParam) {
            list($startDate, $endDate) = explode(" - ", $dateParam);

            $startDate = Carbon::parse($startDate)->format('Y-m-d 00:00:00');
            $endDate = Carbon::parse($endDate)->format('Y-m-d 23:59:59');
        }

        if ($request->isMethod('post')) {
            $branch = Branch::find($branchId);
            return Excel::download(new SalesInvoicesReportExport($branchId, $startDate, $endDate), "$branch->name - Sales Invoices Report.xlsx");
        }

        $transactions = Transaction::where('branch_id', $branchId)
            ->where('is_complete', true)
            ->whereBetween('treg', [$startDate, $endDate])
            ->get();

        $selectedRangeParam = $request->input('selectedRange', 'Today');
        $startDateParam = $request->input('startDate', null);
        $endDateParam = $request->input('endDate', null);

        return view('branch.reports.salesInvoicesReport', compact('transactions', 'branchId', 'dateParam', 'selectedRangeParam', 'startDateParam', 'endDateParam'));
    }
    
    public function salesTransactionReport(Request $request)
    {
        $company = $request->attributes->get('company');
        $branch = $request->attributes->get('branch');

        $branchId = $branch->id;

        $dateParam = $request->input('date_range', null);

        $startDate = Carbon::now()->format('Y-m-d 00:00:00');
        $endDate = Carbon::now()->format('Y-m-d 23:59:59');
        if ($dateParam) {
            list($startDate, $endDate) = explode(" - ", $dateParam);

            $startDate = Carbon::parse($startDate)->format('Y-m-d 00:00:00');
            $endDate = Carbon::parse($endDate)->format('Y-m-d 23:59:59');
        }

        if ($request->isMethod('post')) {
            $branch = Branch::find($branchId);
            return Excel::download(new SalesTransactionReportExport($branchId, $startDate, $endDate), "$branch->name - Sales Transaction Report.xlsx");
        }

        $transactions = Transaction::where('branch_id', $branchId)
            ->where('is_complete', true)
            ->where('is_void', false)
            ->whereBetween('treg', [$startDate, $endDate])
            ->get();

        $selectedRangeParam = $request->input('selectedRange', 'Today');
        $startDateParam = $request->input('startDate', null);
        $endDateParam = $request->input('endDate', null);

        return view('branch.reports.salesTransactionReport', compact('transactions', 'branchId', 'dateParam', 'selectedRangeParam', 'startDateParam', 'endDateParam'));
    }

    public function voidTransactionsReport(Request $request)
    {
        $company = $request->attributes->get('company');

        $branch = $request->attributes->get('branch');

        $branchId = $branch->id;

        $dateParam = $request->input('date_range', null);

        $startDate = Carbon::now()->format('Y-m-d 00:00:00');
        $endDate = Carbon::now()->format('Y-m-d 23:59:59');
        if ($dateParam) {
            list($startDate, $endDate) = explode(" - ", $dateParam);

            $startDate = Carbon::parse($startDate)->format('Y-m-d 00:00:00');
            $endDate = Carbon::parse($endDate)->format('Y-m-d 23:59:59');
        }

        if ($request->isMethod('post')) {
            $branch = Branch::find($branchId);
            return Excel::download(new VoidTransactionsReportExport($branchId, $startDate, $endDate), "$branch->name - Void Transactions Report.xlsx");
        }

        $transactions = Transaction::where([
                'branch_id' => $branchId,
                'is_void' => true,
            ])
            ->whereBetween('treg', [$startDate, $endDate])
            ->get();

        $selectedRangeParam = $request->input('selectedRange', 'Today');
        $startDateParam = $request->input('startDate', null);
        $endDateParam = $request->input('endDate', null);

        return view('branch.reports.voidTransactionsReport', compact('transactions', 'branchId', 'dateParam', 'selectedRangeParam', 'startDateParam', 'endDateParam'));
    }

    public function vatSalesReport(Request $request)
    {
        $company = $request->attributes->get('company');

        $branch = $request->attributes->get('branch');

        $branchId = $branch->id;

        $dateParam = $request->input('date_range', null);

        $startDate = Carbon::now()->format('Y-m-d 00:00:00');
        $endDate = Carbon::now()->format('Y-m-d 23:59:59');
        if ($dateParam) {
            list($startDate, $endDate) = explode(" - ", $dateParam);

            $startDate = Carbon::parse($startDate)->format('Y-m-d 00:00:00');
            $endDate = Carbon::parse($endDate)->format('Y-m-d 23:59:59');
        }

        if ($request->isMethod('post')) {
            $branch = Branch::find($branchId);
            return Excel::download(new VatSalesReportExport($branchId, $startDate, $endDate), "$branch->name - Vat Sales Report.xlsx");
        }

        $transactions = Transaction::where('branch_id', $branchId)
            ->where('is_complete', true)
            ->whereBetween('treg', [$startDate, $endDate])
            ->get();

        $selectedRangeParam = $request->input('selectedRange', 'Today');
        $startDateParam = $request->input('startDate', null);
        $endDateParam = $request->input('endDate', null);

        return view('branch.reports.vatSalesReport', compact('transactions', 'branchId', 'dateParam', 'selectedRangeParam', 'startDateParam', 'endDateParam'));
    }

    public function xReadingReport(Request $request)
    {
        $company = $request->attributes->get('company');

        $branch = $request->attributes->get('branch');

        $branchId = $branch->id;

        $dateParam = $request->input('date_range', null);

        $startDate = Carbon::now()->format('Y-m-d 00:00:00');
        $endDate = Carbon::now()->format('Y-m-d 23:59:59');
        if ($dateParam) {
            list($startDate, $endDate) = explode(" - ", $dateParam);

            $startDate = Carbon::parse($startDate)->format('Y-m-d 00:00:00');
            $endDate = Carbon::parse($endDate)->format('Y-m-d 23:59:59');
        }

        if ($request->isMethod('post')) {
            $branch = Branch::find($branchId);
            return Excel::download(new XReadingReportExport($branchId, $startDate, $endDate), "$branch->name - X Reading Report.xlsx");
        }

        $cutoffs = CutOff::where('branch_id', $branchId)
            ->whereBetween('treg', [$startDate, $endDate])
            ->get();

        $paymentTypes = PaymentType::where('company_id', $company->id)
            ->orWhere('company_id', null)
            ->orderBy('id')
            ->get();

        $discountTypes = DiscountType::where('company_id', $company->id)
            ->orWhere('company_id', null)
            ->orderBy('id')
            ->get();

        $selectedRangeParam = $request->input('selectedRange', 'Today');
        $startDateParam = $request->input('startDate', null);
        $endDateParam = $request->input('endDate', null);

        return view('branch.reports.xReadingReport', compact('cutoffs', 'branchId', 'dateParam', 'paymentTypes', 'discountTypes', 'selectedRangeParam', 'startDateParam', 'endDateParam'));
    }

    public function zReadingReport(Request $request)
    {
        $company = $request->attributes->get('company');

        $branch = $request->attributes->get('branch');

        $branchId = $branch->id;

        $dateParam = $request->input('date_range', null);

        $startDate = Carbon::now()->format('Y-m-d 00:00:00');
        $endDate = Carbon::now()->format('Y-m-d 23:59:59');
        if ($dateParam) {
            list($startDate, $endDate) = explode(" - ", $dateParam);

            $startDate = Carbon::parse($startDate)->format('Y-m-d 00:00:00');
            $endDate = Carbon::parse($endDate)->format('Y-m-d 23:59:59');
        }

        if ($request->isMethod('post')) {
            $branch = Branch::find($branchId);
            return Excel::download(new ZReadingReportExport($branchId, $startDate, $endDate), "$branch->name - Z Reading Report.xlsx");
        }

        $paymentTypes = PaymentType::where('company_id', $company->id)
            ->orWhere('company_id', null)
            ->orderBy('id')
            ->get();

        $discountTypes = DiscountType::where('company_id', $company->id)
            ->orWhere('company_id', null)
            ->orderBy('id')
            ->get();

        $endOfDays = EndOfDay::where('branch_id', $branchId)
            ->whereBetween('treg', [$startDate, $endDate])
            ->get();

        $selectedRangeParam = $request->input('selectedRange', 'Today');
        $startDateParam = $request->input('startDate', null);
        $endDateParam = $request->input('endDate', null);

        return view('branch.reports.zReadingReport', compact('branchId', 'dateParam', 'paymentTypes', 'discountTypes', 'endOfDays', 'selectedRangeParam', 'startDateParam', 'endDateParam'));
    }

    public function discountsReport(Request $request)
    {
        $company = $request->attributes->get('company');

        $branch = $request->attributes->get('branch');

        $branchId = $branch->id;

        $dateParam = $request->input('start_date', date('F Y'));

        $parsedDate = Carbon::parse($dateParam);

        $startDate = $parsedDate->startOfMonth()->format('Y-m-d H:i:s'); // 2024-02-01 00:00:00
        $endDate = $parsedDate->endOfMonth()->format('Y-m-d H:i:s');

        $discountTypes = DiscountType::where('company_id', $company->id)
            ->orWhere('company_id', null)
            ->orderBy('id')
            ->get();

        $filterDiscountTypes = $request->input('discount_types', [$discountTypes->first()->id]);

        if ($request->isMethod('post') && !$request->input('search')) {
            $branch = Branch::find($branchId);
            return Excel::download(new DiscountsReportExport($branchId, $startDate, $endDate, $filterDiscountTypes), "$branch->name - Discounts Report.xlsx");
        }

        $discounts = Discount::select([
                'transactions.completed_at as date',
                'transactions.receipt_number',
                'transactions.gross_sales',
                'transactions.net_sales',
                'discounts.discount_name',
                'discounts.discount_amount',
                'discounts.discount_id',
                'discounts.pos_machine_id',
                'discounts.branch_id',
                'transactions.cashier_name'
            ])
            ->join('transactions', function($join) {
                    $join->on('transactions.transaction_id', '=', 'discounts.transaction_id');
                    $join->on('transactions.branch_id', '=', 'discounts.branch_id');
                    $join->on('transactions.pos_machine_id', '=', 'discounts.pos_machine_id');
            })
            ->whereBetween('discounts.treg', [$startDate, $endDate])
            ->where('discounts.is_void', false)
            ->where('transactions.is_void', false)
            ->where('transactions.is_complete', true)
            ->whereIn('discounts.discount_type_id', $filterDiscountTypes)
            ->where('discounts.branch_id', $branchId)
            ->get();

        return view('branch.reports.discountsReport', compact('company', 'branchId', 'dateParam', 'discountTypes', 'discounts', 'filterDiscountTypes'));
    }

    public function itemSales(Request $request)
    {
        $company = $request->attributes->get('company');
        $branches = $company->activeBranches;

        $branch = $request->attributes->get('branch');

        $branchId = $branch->id;

        $dateParam = $request->input('date_range', null);

        //startDate = 29 days ago
        $startDate = Carbon::now()->format('Y-m-d 00:00:00');
        $endDate = Carbon::now()->format('Y-m-d 23:59:59');
        if ($dateParam) {
            list($startDate, $endDate) = explode(" - ", $dateParam);

            $startDate = Carbon::parse($startDate)->format('Y-m-d 00:00:00');
            $endDate = Carbon::parse($endDate)->format('Y-m-d 23:59:59');
        }

        if ($request->isMethod('post') && !$request->input('search')) {
            $branch = Branch::find($branchId);
            return Excel::download(new ItemSalesReportExport($branchId, $startDate, $endDate), "$branch->name - Item Sales Report - $startDate - $endDate.xlsx");
        }

        $query = "SELECT
                    transactional_db.orders.product_id,
                    products.name AS `product_name`,
                    products.sku,
                    products.cost,
                    products.srp,
                    departments.name AS `department`,
                    SUM(transactional_db.orders.gross) AS gross,
                    SUM(transactional_db.orders.qty) AS qty,
                    SUM(transactional_db.discount_details.discount_amount) AS `discount`,
                    SUM(transactional_db.orders.total) AS `net`
                FROM transactional_db.transactions
                INNER JOIN transactional_db.orders ON transactions.transaction_id = orders.transaction_id
                    AND transactions.branch_id = orders.branch_id
                    AND transactions.pos_machine_id = orders.pos_machine_id
                    AND orders.is_void = FALSE
                    AND orders.is_completed = TRUE
                    AND orders.is_back_out = FALSE
                LEFT JOIN transactional_db.discount_details ON orders.order_id = discount_details.order_id
                    AND orders.branch_id = discount_details.branch_id
                    AND orders.pos_machine_id = discount_details.pos_machine_id
                INNER JOIN isync.products ON orders.product_id = products.id
                INNER JOIN isync.departments on products.department_id = departments.id
                WHERE transactions.is_complete = TRUE
                    AND transactions.branch_id = $branchId
                    AND transactions.is_void = FALSE
                    AND transactions.is_back_out = FALSE
                    AND transactions.treg BETWEEN '$startDate' AND '$endDate'
                GROUP BY orders.product_id";

        $itemSales = DB::select($query);

        // Convert the array of objects into a collection
        $itemSales = collect($itemSales);

        $selectedRangeParam = $request->input('selectedRange', 'Today');
        $startDateParam = $request->input('startDate', null);
        $endDateParam = $request->input('endDate', null);

        return view('branch.reports.itemSales', compact('company', 'branchId', 'dateParam', 'itemSales', 'selectedRangeParam', 'startDateParam', 'endDateParam'));
    }

    public function stockCard(Request $request)
    {
        $company = $request->attributes->get('company');
        $branches = $company->activeBranches;

        $branch = $request->attributes->get('branch');
        $branchId = $branch->id;

        $productId = $request->query('product_id', null);
        $dateParam = $request->input('date_range', null);

        //startDate = 29 days ago
        $startDate = Carbon::now()->format('Y-m-d 00:00:00');
        $endDate = Carbon::now()->format('Y-m-d 23:59:59');
        if ($dateParam) {
            list($startDate, $endDate) = explode(" - ", $dateParam);

            $startDate = Carbon::parse($startDate)->format('Y-m-d 00:00:00');
            $endDate = Carbon::parse($endDate)->format('Y-m-d 23:59:59');
        }

        $product = [];
        $pivotData = [];
        $transactions = [];
        $physicalCounts = [];
        $incomingStocks = [];
        $stockTransferIn = [];
        $stockTransferOut = [];
        $disposals = [];
        if ($productId) {
            $product = Product::findOrFail($productId);

            $pivotData = $product->branches->where('id', $branchId)->first()?->pivot;

            $transactionQuery = "
                SELECT
                    transactions.treg as `transaction_date`,
                    transactions.receipt_number,
                    orders.qty,
                    unit_of_measurements.name as `unit`,
                    orders.gross,
                    orders.cost,
                    orders.gross - orders.total_cost as `profit`
                FROM transactional_db.transactions
                INNER JOIN transactional_db.orders ON transactions.transaction_id = orders.transaction_id
                    AND transactions.branch_id = orders.branch_id
                    AND transactions.pos_machine_id = orders.pos_machine_id
                    AND orders.is_void = FALSE
                    AND orders.is_completed = TRUE
                    AND orders.is_back_out = FALSE
                INNER JOIN isync.products ON orders.product_id = products.id
                INNER JOIN isync.unit_of_measurements on orders.unit_id = unit_of_measurements.id
                WHERE transactions.is_complete = TRUE
                    AND transactions.branch_id = $branchId
                    AND transactions.is_void = FALSE
                    AND transactions.is_back_out = FALSE
                    AND orders.product_id = $productId
                    -- AND transactions.treg BETWEEN '$startDate' AND '$endDate'
                ";

            $transactions = DB::select($transactionQuery);

            $physicalCountQuery = "
                    SELECT
                        product_physical_counts.created_at AS `physical_count_date`,
                        product_physical_count_items.quantity,
                        product_count_logs.old_quantity,
                        product_count_logs.new_quantity,
                        CONCAT(createdBy.first_name, ' ', createdBy.last_name) AS `created_by`,
                        CONCAT(actionBy.first_name, ' ', actionBy.last_name) AS `action_by`,
                        unit_of_measurements.`name` AS `uom`
                    FROM product_physical_counts
                    INNER JOIN product_physical_count_items ON product_physical_counts.id = product_physical_count_items.product_physical_count_id
                    INNER JOIN users createdBy ON product_physical_counts.created_by = createdBy.id
                    INNER JOIN unit_of_measurements ON product_physical_count_items.uom_id = unit_of_measurements.id
                    LEFT JOIN users actionBy ON product_physical_counts.action_by = actionBy.id
                    LEFT JOIN product_count_logs ON product_physical_count_items.product_id = product_count_logs.product_id
                        AND product_count_logs.branch_id = product_physical_counts.branch_id
                        AND product_count_logs.object_id = product_physical_counts.id
                        AND product_count_logs.object_type = 'product_physical_counts'
                    WHERE product_physical_count_items.product_id = $productId
                        AND product_physical_counts.branch_id = $branchId
                        AND product_physical_counts.created_at BETWEEN '$startDate' AND '$endDate'
                ";

            $physicalCounts = DB::select($physicalCountQuery);

            $incomingStocksQuery = "
                SELECT purchase_deliveries.created_at AS `delivery_date`,
                    purchase_orders.po_number,
                    purchase_deliveries.pd_number,
                    suppliers.`name` AS `supplier`,
                    purchase_deliveries.sales_invoice_number,
                    purchase_delivery_items.qty,
                    purchase_delivery_items.unit_price,
                    CONCAT(createdBy.first_name, ' ', createdBy.last_name) AS `created_by`,
                    CONCAT(actionBy.first_name, ' ', actionBy.last_name) AS `action_by`
                FROM purchase_deliveries
                INNER JOIN purchase_delivery_items ON purchase_deliveries.id = purchase_delivery_items.purchase_delivery_id
                INNER JOIN purchase_orders ON purchase_deliveries.purchase_order_id = purchase_orders.id
                INNER JOIN suppliers ON purchase_orders.supplier_id = suppliers.id
                INNER JOIN users createdBy ON purchase_deliveries.created_by = createdBy.id
                LEFT JOIN users actionBy ON purchase_deliveries.action_by = actionBy.id
                WHERE purchase_deliveries.status = 'approved'
                    AND purchase_delivery_items.product_id = $productId
                    AND purchase_deliveries.branch_id = $branchId
                    AND purchase_deliveries.created_at BETWEEN '$startDate' AND '$endDate'
            ";

            $incomingStocks = DB::select($incomingStocksQuery);

            $stockTransferInQuery = "
                SELECT stock_transfer_deliveries.created_at AS `delivery_date`,
                    stock_transfer_orders.sto_number,
                    stock_transfer_deliveries.std_number,
                    source_branch.name AS `source_branch`,
                    destination_branch.name AS `destination_branch`,
                    stock_transfer_delivery_items.qty,
                    unit_of_measurements.`name` AS `uom`,
                    products.cost,
                    CONCAT(requestedBy.first_name, ' ', requestedBy.last_name) AS `requested_by`,
                    CONCAT(approvedBy.first_name, ' ', approvedBy.last_name) AS `approved_by`,
                    CONCAT(receivedBy.first_name, ' ', receivedBy.last_name) AS `received_by`
                FROM stock_transfer_deliveries
                INNER JOIN stock_transfer_delivery_items ON stock_transfer_delivery_items.stock_transfer_delivery_id = stock_transfer_deliveries.id
                INNER JOIN stock_transfer_orders ON stock_transfer_deliveries.stock_transfer_order_id = stock_transfer_orders.id
                INNER JOIN stock_transfer_requests ON stock_transfer_orders.stock_transfer_request_id = stock_transfer_requests.id
                INNER JOIN branches AS source_branch ON stock_transfer_requests.source_branch_id = source_branch.id
                INNER JOIN branches AS destination_branch ON stock_transfer_requests.destination_branch_id = destination_branch.id
                INNER JOIN unit_of_measurements ON stock_transfer_delivery_items.uom_id = unit_of_measurements.id
                INNER JOIN products ON stock_transfer_delivery_items.product_id = products.id
                INNER JOIN users AS requestedBy ON stock_transfer_requests.created_by = requestedBy.id
                INNER JOIN users AS approvedBy ON stock_transfer_requests.action_by = approvedBy.id
                INNER JOIN users AS receivedBy ON stock_transfer_deliveries.created_by = receivedBy.id 
                WHERE stock_transfer_deliveries.status = 'approved'
                    AND stock_transfer_delivery_items.product_id = $productId
                    AND stock_transfer_requests.destination_branch_id = $branchId
                    AND stock_transfer_requests.created_at BETWEEN '$startDate' AND '$endDate'
            ";

            $stockTransferIn = DB::select($stockTransferInQuery);

            $stockTransferOutQuery = "
                SELECT stock_transfer_deliveries.created_at AS `delivery_date`,
                    stock_transfer_orders.sto_number,
                    stock_transfer_deliveries.std_number,
                    source_branch.name AS `source_branch`,
                    destination_branch.name AS `destination_branch`,
                    stock_transfer_delivery_items.qty,
                    unit_of_measurements.`name` AS `uom`,
                    products.cost,
                    CONCAT(requestedBy.first_name, ' ', requestedBy.last_name) AS `requested_by`,
                    CONCAT(approvedBy.first_name, ' ', approvedBy.last_name) AS `approved_by`,
                    CONCAT(receivedBy.first_name, ' ', receivedBy.last_name) AS `received_by`
                FROM stock_transfer_deliveries
                INNER JOIN stock_transfer_delivery_items ON stock_transfer_delivery_items.stock_transfer_delivery_id = stock_transfer_deliveries.id
                INNER JOIN stock_transfer_orders ON stock_transfer_deliveries.stock_transfer_order_id = stock_transfer_orders.id
                INNER JOIN stock_transfer_requests ON stock_transfer_orders.stock_transfer_request_id = stock_transfer_requests.id
                INNER JOIN branches AS source_branch ON stock_transfer_requests.source_branch_id = source_branch.id
                INNER JOIN branches AS destination_branch ON stock_transfer_requests.destination_branch_id = destination_branch.id
                INNER JOIN unit_of_measurements ON stock_transfer_delivery_items.uom_id = unit_of_measurements.id
                INNER JOIN products ON stock_transfer_delivery_items.product_id = products.id
                INNER JOIN users AS requestedBy ON stock_transfer_requests.created_by = requestedBy.id
                INNER JOIN users AS approvedBy ON stock_transfer_requests.action_by = approvedBy.id
                INNER JOIN users AS receivedBy ON stock_transfer_deliveries.created_by = receivedBy.id 
                WHERE stock_transfer_deliveries.status = 'approved'
                    AND stock_transfer_delivery_items.product_id = $productId
                    AND stock_transfer_requests.source_branch_id = $branchId
                    AND stock_transfer_requests.created_at BETWEEN '$startDate' AND '$endDate'
            ";

            $stockTransferOut = DB::select($stockTransferOutQuery);

            $disposalQuery = "
                SELECT product_disposals.created_at AS `date`,
                    product_disposal_items.quantity,
                    unit_of_measurements.`name` AS `uom`,
                    products.cost,
                    product_disposal_reasons.`name` AS `reason`,
                    CONCAT(createdBy.first_name, ' ', createdBy.last_name) AS `created_by`,
                    CONCAT(actionBy.first_name, ' ', actionBy.last_name) AS `action_by`
                FROM product_disposals
                INNER JOIN product_disposal_items ON product_disposal_items.product_disposal_id = product_disposals.id
                INNER JOIN unit_of_measurements ON product_disposal_items.uom_id = unit_of_measurements.id
                INNER JOIN products ON product_disposal_items.product_id = products.id
                INNER JOIN product_disposal_reasons ON product_disposals.product_disposal_reason_id = product_disposal_reasons.id
                INNER JOIN users createdBy ON product_disposals.created_by = createdBy.id
                LEFT JOIN users actionBy ON product_disposals.action_by = actionBy.id
                WHERE product_disposals.status = 'approved'
                    AND product_disposal_items.product_id = $productId
                    AND product_disposals.created_at BETWEEN '$startDate' AND '$endDate'
            ";

            // echo($disposalQuery);
            // die();
            $disposals = DB::select($disposalQuery);
        }

        $selectedRangeParam = $request->input('selectedRange', 'Today');
        $startDateParam = $request->input('startDate', null);
        $endDateParam = $request->input('endDate', null);

        return view('branch.reports.stockCard', compact(
            'company',
            'branches',
            'branchId',
            'productId',
            'product',
            'pivotData',
            'physicalCounts',
            'transactions',
            'incomingStocks',
            'stockTransferIn',
            'stockTransferOut',
            'disposals',
            'selectedRangeParam',
            'startDateParam',
            'endDateParam',
        ));
    }
}
