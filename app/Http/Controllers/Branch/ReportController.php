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
use App\Models\PosMachine;
use App\Models\AuditTrail;

use Carbon\Carbon;

use App\Exports\SalesTransactionReportExport;
use App\Exports\VoidTransactionsReportExport;
use App\Exports\VatSalesReportExport;
use App\Exports\XReadingReportExport;
use App\Exports\ZReadingReportExport;
use App\Exports\SalesInvoicesReportExport;
use App\Exports\DiscountsReportExport;
use App\Exports\ItemSalesReportExport;
use App\Exports\AuditTrailReportExport;
use App\Exports\BackupExport;

use Maatwebsite\Excel\Facades\Excel;

use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    /**
     * Determine the selected range based on start and end dates
     *
     * @param string|null $startDateParam
     * @param string|null $endDateParam
     * @return string
     */
    private function determineSelectedRange($startDateParam, $endDateParam)
    {
        if (!$startDateParam || !$endDateParam) {
            return 'Today';
        }

        $start = Carbon::parse($startDateParam)->startOfDay();
        $end = Carbon::parse($endDateParam)->endOfDay();
        $now = Carbon::now();

        // Check for Today
        if ($start->isSameDay($now) && $end->isSameDay($now)) {
            return 'Today';
        }

        // Check for Yesterday
        $yesterday = $now->copy()->subDay();
        if ($start->isSameDay($yesterday) && $end->isSameDay($yesterday)) {
            return 'Yesterday';
        }

        // Check for This Week (Sunday to Saturday)
        $weekStart = $now->copy()->startOfWeek();
        $weekEnd = $now->copy()->endOfWeek();
        if ($start->isSameDay($weekStart) && $end->isSameDay($weekEnd)) {
            return 'This Week';
        }

        // Check for Last 7 Days
        $sevenDaysAgo = $now->copy()->subDays(7)->startOfDay();
        if ($start->isSameDay($sevenDaysAgo) && $end->isSameDay($now)) {
            return 'Last 7 days';
        }

        // Check for This Month
        $monthStart = $now->copy()->startOfMonth();
        $monthEnd = $now->copy()->endOfMonth();
        if ($start->isSameDay($monthStart) && $end->isSameDay($monthEnd)) {
            return 'This Month';
        }

        // Check for Last Month
        $lastMonth = $now->copy()->subMonth();
        $lastMonthStart = $lastMonth->copy()->startOfMonth();
        $lastMonthEnd = $lastMonth->copy()->endOfMonth();
        if ($start->isSameDay($lastMonthStart) && $end->isSameDay($lastMonthEnd)) {
            return 'Last Month';
        }

        // Check for Last 30 Days
        $thirtyDaysAgo = $now->copy()->subDays(30)->startOfDay();
        if ($start->isSameDay($thirtyDaysAgo) && $end->isSameDay($now)) {
            return 'Last 30 days';
        }

        // Check for This Year
        $yearStart = $now->copy()->startOfYear();
        $yearEnd = $now->copy()->endOfYear();
        if ($start->isSameDay($yearStart) && $end->isSameDay($yearEnd)) {
            return 'This Year';
        }

        // Check for Last Year
        $lastYear = $now->copy()->subYear();
        $lastYearStart = $lastYear->copy()->startOfYear();
        $lastYearEnd = $lastYear->copy()->endOfYear();
        if ($start->isSameDay($lastYearStart) && $end->isSameDay($lastYearEnd)) {
            return 'Last Year';
        }

        // Default to Custom Range
        return 'Custom Range';
    }

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

        $startDateParam = $request->input('startDate', null);
        $endDateParam = $request->input('endDate', null);
        $selectedRangeParam = $this->determineSelectedRange($startDateParam, $endDateParam);

        return view('branch.reports.salesInvoicesReport', compact('transactions', 'branchId', 'dateParam', 'selectedRangeParam', 'startDateParam', 'endDateParam', 'branch', 'company'));
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

        $startDateParam = $request->input('startDate', null);
        $endDateParam = $request->input('endDate', null);
        $selectedRangeParam = $this->determineSelectedRange($startDateParam, $endDateParam);

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

        $query = Transaction::where([
            'transactions.branch_id' => $branchId,
            'transactions.is_void' => true,
        ])
        ->whereBetween('transactions.treg', [$startDate, $endDate]);

        $paymentTypeId = $request->query('payment_type_id', null);
        if ($paymentTypeId) {
            // Join payments if paymentTypeId is provided
            $query->join('payments', function($join) {
                $join->on('transactions.transaction_id', '=', 'payments.transaction_id');
                $join->on('transactions.branch_id', '=', 'payments.branch_id');
                $join->on('transactions.pos_machine_id', '=', 'payments.pos_machine_id');
            });

            $query->where('payments.payment_type_id', $paymentTypeId);

            $query->groupBy('transactions.id');
        }

        // Fetch the results
        $transactions = $query->select('transactions.*')->get(); // Ensure you're selecting valid columns

        $startDateParam = $request->input('startDate', null);
        $endDateParam = $request->input('endDate', null);
        $selectedRangeParam = $this->determineSelectedRange($startDateParam, $endDateParam);

        $paymentTypes = PaymentType::where('company_id', $company->id)
            ->orWhereNull('company_id')
            ->where('status', 'active')
            ->with('fields')
            ->orderBy('name')
            ->get();

        return view('branch.reports.voidTransactionsReport', compact('transactions', 'branchId', 'dateParam', 'selectedRangeParam', 'startDateParam', 'endDateParam', 'paymentTypes', 'paymentTypeId'));
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
            ->where('is_void', false)
            ->whereBetween('treg', [$startDate, $endDate])
            ->get();

        $startDateParam = $request->input('startDate', null);
        $endDateParam = $request->input('endDate', null);
        $selectedRangeParam = $this->determineSelectedRange($startDateParam, $endDateParam);

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

        $startDateParam = $request->input('startDate', null);
        $endDateParam = $request->input('endDate', null);
        $selectedRangeParam = $this->determineSelectedRange($startDateParam, $endDateParam);

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

        $startDateParam = $request->input('startDate', null);
        $endDateParam = $request->input('endDate', null);
        $selectedRangeParam = $this->determineSelectedRange($startDateParam, $endDateParam);

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
                'transactions.cashier_name',
                'pos_machines.machine_number'
            ])
            ->join('transactions', function($join) {
                    $join->on('transactions.transaction_id', '=', 'discounts.transaction_id');
                    $join->on('transactions.branch_id', '=', 'discounts.branch_id');
                    $join->on('transactions.pos_machine_id', '=', 'discounts.pos_machine_id');
            })
            ->join('isync.pos_machines', 'transactions.pos_machine_id', '=', 'pos_machines.id')
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
        $transactionalDbName = config('database.connections.transactional_db.database');
        $isyncDbName = config('database.connections.mysql.database');

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
                    $transactionalDbName.orders.product_id,
                    products.name AS `product_name`,
                    products.sku,
                    products.cost,
                    products.srp,
                    departments.name AS `department`,
                    SUM($transactionalDbName.orders.gross) AS gross,
                    SUM($transactionalDbName.orders.qty) AS qty,
                    SUM($transactionalDbName.discount_details.discount_amount) AS `discount`,
                    SUM($transactionalDbName.orders.total) AS `net`
                FROM $transactionalDbName.transactions
                INNER JOIN $transactionalDbName.orders ON transactions.transaction_id = orders.transaction_id
                    AND transactions.branch_id = orders.branch_id
                    AND transactions.pos_machine_id = orders.pos_machine_id
                    AND orders.is_void = FALSE
                    AND orders.is_completed = TRUE
                    AND orders.is_back_out = FALSE
                LEFT JOIN $transactionalDbName.discount_details ON orders.order_id = discount_details.order_id
                    AND orders.branch_id = discount_details.branch_id
                    AND orders.pos_machine_id = discount_details.pos_machine_id
                INNER JOIN $isyncDbName.products ON orders.product_id = products.id
                INNER JOIN $isyncDbName.departments on products.department_id = departments.id
                WHERE transactions.is_complete = TRUE
                    AND transactions.branch_id = $branchId
                    AND transactions.is_void = FALSE
                    AND transactions.is_back_out = FALSE
                    AND transactions.treg BETWEEN '$startDate' AND '$endDate'
                GROUP BY orders.product_id";

        $itemSales = DB::select($query);

        // Convert the array of objects into a collection
        $itemSales = collect($itemSales);

        $startDateParam = $request->input('startDate', null);
        $endDateParam = $request->input('endDate', null);
        $selectedRangeParam = $this->determineSelectedRange($startDateParam, $endDateParam);

        return view('branch.reports.itemSales', compact('company', 'branchId', 'dateParam', 'itemSales', 'selectedRangeParam', 'startDateParam', 'endDateParam'));
    }

    public function stockCard(Request $request)
    {
        $transactionalDbName = config('database.connections.transactional_db.database');
        $defaultDbName = config('database.connections.mysql.database');

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
                    orders.gross - (orders.cost * orders.qty) as profit
                FROM $transactionalDbName.transactions
                INNER JOIN $transactionalDbName.orders ON transactions.transaction_id = orders.transaction_id
                    AND transactions.branch_id = orders.branch_id
                    AND transactions.pos_machine_id = orders.pos_machine_id
                    AND orders.is_void = FALSE
                    AND orders.is_completed = TRUE
                    AND orders.is_back_out = FALSE
                INNER JOIN $defaultDbName.products ON orders.product_id = products.id
                INNER JOIN $defaultDbName.unit_of_measurements on orders.unit_id = unit_of_measurements.id
                WHERE transactions.is_complete = TRUE
                    AND transactions.branch_id = $branchId
                    AND transactions.is_void = FALSE
                    AND transactions.is_back_out = FALSE
                    AND orders.product_id = $productId
                    AND transactions.treg BETWEEN '$startDate' AND '$endDate'
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
                        unit_of_measurements.`name` AS `uom`,
                        product_physical_counts.pcount_number
                    FROM $defaultDbName.product_physical_counts
                    INNER JOIN $defaultDbName.product_physical_count_items ON product_physical_counts.id = product_physical_count_items.product_physical_count_id
                    INNER JOIN $defaultDbName.users createdBy ON product_physical_counts.created_by = createdBy.id
                    INNER JOIN $defaultDbName.unit_of_measurements ON product_physical_count_items.uom_id = unit_of_measurements.id
                    LEFT JOIN $defaultDbName.users actionBy ON product_physical_counts.action_by = actionBy.id
                    LEFT JOIN $defaultDbName.product_count_logs ON product_physical_count_items.product_id = product_count_logs.product_id
                        AND product_count_logs.branch_id = product_physical_counts.branch_id
                        AND product_count_logs.object_id = product_physical_counts.id
                        AND product_count_logs.object_type = 'product_physical_counts'
                    WHERE product_physical_count_items.product_id = $productId
                        AND product_physical_counts.branch_id = $branchId
                        AND product_physical_counts.created_at BETWEEN '$startDate' AND '$endDate'
                        AND product_physical_counts.status = 'approved'
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
                FROM $defaultDbName.purchase_deliveries
                INNER JOIN $defaultDbName.purchase_delivery_items ON purchase_deliveries.id = purchase_delivery_items.purchase_delivery_id
                INNER JOIN $defaultDbName.purchase_orders ON purchase_deliveries.purchase_order_id = purchase_orders.id
                INNER JOIN $defaultDbName.suppliers ON purchase_orders.supplier_id = suppliers.id
                INNER JOIN $defaultDbName.users createdBy ON purchase_deliveries.created_by = createdBy.id
                LEFT JOIN $defaultDbName.users actionBy ON purchase_deliveries.action_by = actionBy.id
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
                FROM $defaultDbName.stock_transfer_deliveries
                INNER JOIN $defaultDbName.stock_transfer_delivery_items ON stock_transfer_delivery_items.stock_transfer_delivery_id = stock_transfer_deliveries.id
                INNER JOIN $defaultDbName.stock_transfer_orders ON stock_transfer_deliveries.stock_transfer_order_id = stock_transfer_orders.id
                INNER JOIN $defaultDbName.stock_transfer_requests ON stock_transfer_orders.stock_transfer_request_id = stock_transfer_requests.id
                INNER JOIN $defaultDbName.branches AS source_branch ON stock_transfer_requests.source_branch_id = source_branch.id
                INNER JOIN $defaultDbName.branches AS destination_branch ON stock_transfer_requests.destination_branch_id = destination_branch.id
                INNER JOIN $defaultDbName.unit_of_measurements ON stock_transfer_delivery_items.uom_id = unit_of_measurements.id
                INNER JOIN $defaultDbName.products ON stock_transfer_delivery_items.product_id = products.id
                INNER JOIN $defaultDbName.users AS requestedBy ON stock_transfer_requests.created_by = requestedBy.id
                INNER JOIN $defaultDbName.users AS approvedBy ON stock_transfer_requests.action_by = approvedBy.id
                INNER JOIN $defaultDbName.users AS receivedBy ON stock_transfer_deliveries.created_by = receivedBy.id 
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
                FROM $defaultDbName.stock_transfer_deliveries
                INNER JOIN $defaultDbName.stock_transfer_delivery_items ON stock_transfer_delivery_items.stock_transfer_delivery_id = stock_transfer_deliveries.id
                INNER JOIN $defaultDbName.stock_transfer_orders ON stock_transfer_deliveries.stock_transfer_order_id = stock_transfer_orders.id
                INNER JOIN $defaultDbName.stock_transfer_requests ON stock_transfer_orders.stock_transfer_request_id = stock_transfer_requests.id
                INNER JOIN $defaultDbName.branches AS source_branch ON stock_transfer_requests.source_branch_id = source_branch.id
                INNER JOIN $defaultDbName.branches AS destination_branch ON stock_transfer_requests.destination_branch_id = destination_branch.id
                INNER JOIN $defaultDbName.unit_of_measurements ON stock_transfer_delivery_items.uom_id = unit_of_measurements.id
                INNER JOIN $defaultDbName.products ON stock_transfer_delivery_items.product_id = products.id
                INNER JOIN $defaultDbName.users AS requestedBy ON stock_transfer_requests.created_by = requestedBy.id
                INNER JOIN $defaultDbName.users AS approvedBy ON stock_transfer_requests.action_by = approvedBy.id
                INNER JOIN $defaultDbName.users AS receivedBy ON stock_transfer_deliveries.created_by = receivedBy.id 
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
                    CONCAT(actionBy.first_name, ' ', actionBy.last_name) AS `action_by`,
                    product_disposals.pdis_number
                FROM $defaultDbName.product_disposals
                INNER JOIN $defaultDbName.product_disposal_items ON product_disposal_items.product_disposal_id = product_disposals.id
                INNER JOIN $defaultDbName.unit_of_measurements ON product_disposal_items.uom_id = unit_of_measurements.id
                INNER JOIN $defaultDbName.products ON product_disposal_items.product_id = products.id
                INNER JOIN $defaultDbName.product_disposal_reasons ON product_disposals.product_disposal_reason_id = product_disposal_reasons.id
                INNER JOIN $defaultDbName.users createdBy ON product_disposals.created_by = createdBy.id
                LEFT JOIN $defaultDbName.users actionBy ON product_disposals.action_by = actionBy.id
                WHERE product_disposals.status = 'approved'
                    AND product_disposal_items.product_id = $productId
                    AND product_disposals.branch_id = $branchId
                    AND product_disposals.created_at BETWEEN '$startDate' AND '$endDate'
            ";

            // echo($disposalQuery);
            // die();
            $disposals = DB::select($disposalQuery);
        }

        $startDateParam = $request->input('startDate', null);
        $endDateParam = $request->input('endDate', null);
        $selectedRangeParam = $this->determineSelectedRange($startDateParam, $endDateParam);

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

    public function auditTrail(Request $request)
    {
        $company = $request->attributes->get('company');
        $branch = $request->attributes->get('branch');

        $machineIds = [];
        $machines = [];
        foreach($branch->machines as $machine) {
            if ($machine->type == 'cashier') {
                $machineIds[] = $machine->id;
                $machines[$machine->id] = $machine;
            }
        }

        $dateParam = $request->input('date_range', null);
        $machineId = $request->input('machine_id', $machineIds[0]);

        $startDate = Carbon::now()->format('Y-m-d 00:00:00');
        $endDate = Carbon::now()->format('Y-m-d 23:59:59');
        if ($dateParam) {
            list($startDate, $endDate) = explode(" - ", $dateParam);

            $startDate = Carbon::parse($startDate)->format('Y-m-d 00:00:00');
            $endDate = Carbon::parse($endDate)->format('Y-m-d 23:59:59');
        }

        if ($request->isMethod('post') && !$request->input('search')) {
            $machine = PosMachine::find($machineId);
            return Excel::download(new AuditTrailReportExport($machineId, $startDate, $endDate), "$machine->name - Audit Trail.xlsx");
        }

        $trails = AuditTrail::where('pos_machine_id', $machineId)
            ->whereBetween('treg', [$startDate, $endDate])
            ->get();

        $startDateParam = $request->input('startDate', null);
        $endDateParam = $request->input('endDate', null);
        $selectedRangeParam = $this->determineSelectedRange($startDateParam, $endDateParam);

        return view('branch.reports.auditTrail', compact(
            'company',
            'trails',
            'machines',
            'selectedRangeParam',
            'startDateParam',
            'endDateParam',
            'machineId'
        ));
    }

    public function backup(Request $request)
    {
        $company = $request->attributes->get('company');
        $branch = $request->attributes->get('branch');

        return Excel::download(new BackupExport($branch->id), "$company->name - $branch->name - ".Carbon::now()->format('Y-m-d 23:59:59')." - Backup.xlsx");
    }

    public function accountReceivables(Request $request)
    {
        $company = $request->attributes->get('company');
        $branch = $request->attributes->get('branch');

        $query = "
                SELECT
                    transactions.charge_account_id,
                    isync.charge_accounts.`name`,
                    isync.charge_accounts.address,
                    SUM(transactions.gross_sales) AS `total_sales`,
                    SUM(CASE WHEN transactions.is_account_receivable_redeem = TRUE THEN transactions.gross_sales ELSE 0 END) AS `redeemed_sales`,
                    SUM(CASE WHEN transactions.is_account_receivable_redeem = FALSE THEN transactions.gross_sales ELSE 0 END) AS `not_redeemed_sales`
                FROM transactional_db.transactions
                INNER JOIN isync.charge_accounts ON transactions.charge_account_id = charge_accounts.id
                WHERE transactions.is_account_receivable = TRUE
                AND transactions.is_void = FALSE
                AND transactions.is_back_out = FALSE
                AND transactions.is_complete = TRUE
                GROUP BY transactions.charge_account_id;
            ";

        $accountReceivables = DB::select($query);

        return view('branch.reports.ar', compact(
            'accountReceivables',
            'company',
            'branch'
        ));
    }

    public function accountReceivableDetails(Request $request, $companySlug, $branchSlug, $customerId)
    {
        $company = $request->attributes->get('company');
        $branch = $request->attributes->get('branch');

        $query = "
                SELECT
                    transactions.charge_account_id,
                    isync.charge_accounts.`name`,
                    isync.charge_accounts.address,
                    SUM(transactions.gross_sales) AS `total_sales`,
                    SUM(CASE WHEN transactions.is_account_receivable_redeem = TRUE THEN transactions.gross_sales ELSE 0 END) AS `redeemed_sales`,
                    SUM(CASE WHEN transactions.is_account_receivable_redeem = FALSE THEN transactions.gross_sales ELSE 0 END) AS `not_redeemed_sales`
                FROM transactional_db.transactions
                INNER JOIN isync.charge_accounts ON transactions.charge_account_id = charge_accounts.id
                WHERE transactions.is_account_receivable = TRUE
                AND transactions.is_void = FALSE
                AND transactions.is_back_out = FALSE
                AND transactions.is_complete = TRUE
                AND transactions.charge_account_id = $customerId;
            ";

        $accountReceivables = DB::select($query);

        $transactionsQuery = "
                SELECT
	                transactions.id,
                    transactions.receipt_number,
                    transactions.completed_at,
                    orders.`description` AS `item_description`,
                    orders.qty,
                    unit_of_measurements.name AS `uom`,
                    orders.gross,
                    orders.discount_amount,
                    transactions.cashier_name
                FROM
                    transactional_db.transactions
                    INNER JOIN transactional_db.orders ON transactions.transaction_id = orders.transaction_id
                    AND orders.branch_id = transactions.branch_id
                    AND transactions.pos_machine_id = orders.pos_machine_id
                    LEFT JOIN isync.unit_of_measurements ON orders.unit_id = unit_of_measurements.id
                WHERE
                    transactions.is_account_receivable = TRUE
                    AND transactions.is_void = FALSE
                    AND transactions.is_back_out = FALSE
                    AND transactions.is_complete = TRUE
                    AND transactions.charge_account_id = $customerId
            ";

        $transactions = DB::select($transactionsQuery);

        return view('branch.reports.arDetails', compact(
            'accountReceivables',
            'company',
            'branch',
            'transactions'
        ));
    }
}
