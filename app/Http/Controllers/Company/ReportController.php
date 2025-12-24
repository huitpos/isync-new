<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

use App\Models\Transaction;
use App\Models\CutOff;
use App\Models\PaymentType;
use App\Models\DiscountType;
use App\Models\EndOfDay;
use App\Models\Discount;
use App\Models\Branch;
use App\Models\Product;

use App\DataTables\Company\Reports\TransactionsDataTable;
use App\Exports\TestExport;
use App\Exports\SalesTransactionReportExport;
use App\Exports\VoidTransactionsReportExport;
use App\Exports\VatSalesReportExport;
use App\Exports\XReadingReportExport;
use App\Exports\ZReadingReportExport;
use App\Exports\SalesInvoicesReportExport;
use App\Exports\DiscountsReportExport;
use App\Exports\ItemSalesReportExport;
use App\Exports\AuditTrailReportExport;
use App\Exports\BirSalesSummaryReportExport;
use App\Exports\BirSeniorSalesReportExport;
use App\Exports\BirPwdSalesReportExport;
use App\Exports\BirNaacSalesReportExport;
use App\Exports\BirSoloParentSalesReportExport;
use App\Exports\CategorySalesReportExport;
use App\Exports\DepartmentSalesReportExport;
use App\Exports\SalesReturnReportExport;
use App\Exports\SubCategorySalesReportExport;
use App\Exports\HourlySalesReportExport;
use App\Exports\HourlySalesSummaryExport;
use App\Exports\MonthlySalesSummaryExport;
use App\Exports\TopPerformingProductsExport;
use App\Exports\SafekeepingReportExport;
use App\Models\AuditTrail;
use App\Models\PosMachine;
use Illuminate\Support\Facades\DB;

use Carbon\Carbon;

class ReportController extends Controller
{
    public function transactions(Request $request, TransactionsDataTable $dataTable)
    {
        $company = $request->attributes->get('company');
        return $dataTable->with('company_id', $company->id)
            ->render('company.reports.transactions', compact('company'));
    }

    public function viewTransaction(Request $request, $companySlug, $id)
    {
        $company = $request->attributes->get('company');
        $branches = $company->branches;

        $branchIds = $branches->pluck('id')->unique();

        $transaction = Transaction::where(['id' => $id])
            ->whereIn('branch_id', $branchIds)
            ->first();
        if (!$transaction) {
            abort(404);
        }

        return view('company.reports.viewTransaction', compact('company', 'transaction'));
    }

    /**
     * Export users with a custom query and data manipulation based on URL parameters.
     *
     * @return \Illuminate\Support\Collection
     */
    public function exportCustomUsers(Request $request)
    {
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');

        return Excel::download(new TestExport($startDate, $endDate), 'custom_users.xlsx');
    }

    public function salesInvoicesReport(Request $request)
    {
        $company = $request->attributes->get('company');

        $branches = $company->activeBranches;
        
        $branchId = $request->query('branch_id', $branches->first()->id);

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
            
            ini_set('max_execution_time', 0);

            return Excel::download(new SalesInvoicesReportExport($branchId, $startDate, $endDate), "$branch->name - Sales Invoices Report.xlsx");
        }

        $transactions = Transaction::where('branch_id', $branchId)
            ->where('is_complete', true)
            ->where('is_account_receivable', false)
            ->whereBetween('completed_at', [$startDate, $endDate])
            ->get();

        $selectedRangeParam = $request->input('selectedRange', 'Today');
        $startDateParam = $request->input('startDate', null);
        $endDateParam = $request->input('endDate', null);

        return view('company.reports.salesInvoicesReport', compact('company', 'branches', 'transactions', 'branchId', 'dateParam', 'selectedRangeParam', 'startDateParam', 'endDateParam'));
    }

    public function salesTransactionReport(Request $request)
    {
        $company = $request->attributes->get('company');

        $branches = $company->activeBranches;

        $branchId = $request->query('branch_id', $branches->first()->id);

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
            ->whereBetween('completed_at', [$startDate, $endDate])
            ->get();

        $selectedRangeParam = $request->input('selectedRange', 'Today');
        $startDateParam = $request->input('startDate', null);
        $endDateParam = $request->input('endDate', null);

        return view('company.reports.salesTransactionReport', compact('company', 'branches', 'transactions', 'branchId', 'dateParam', 'selectedRangeParam', 'startDateParam', 'endDateParam', 'selectedRangeParam', 'startDateParam', 'endDateParam'));
    }

    public function voidTransactionsReport(Request $request)
    {
        $company = $request->attributes->get('company');

        $branches = $company->activeBranches;

        $branchId = $request->query('branch_id', $branches->first()->id);
        $paymentTypeId = $request->query('payment_type_id', null);

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

        $paymentTypes = PaymentType::where('company_id', $company->id)
            ->orWhereNull('company_id')
            ->where('status', 'active')
            ->with('fields')
            ->orderBy('name')
            ->get();

        $query = Transaction::where([
            'transactions.branch_id' => $branchId,
            'transactions.is_void' => true,
        ])
        ->whereBetween('transactions.treg', [$startDate, $endDate]);

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

        $selectedRangeParam = $request->input('selectedRange', 'Today');
        $startDateParam = $request->input('startDate', null);
        $endDateParam = $request->input('endDate', null);

        return view('company.reports.voidTransactionsReport', compact('company', 'branches', 'transactions', 'branchId', 'dateParam', 'selectedRangeParam', 'startDateParam', 'endDateParam', 'paymentTypes', 'paymentTypeId'));
    }

    public function vatSalesReport(Request $request)
    {
        $company = $request->attributes->get('company');

        $branches = $company->activeBranches;

        $branchId = $request->query('branch_id', $branches->first()->id);

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
            ->where('is_account_receivable', false)
            ->where('is_void', false)
            ->whereBetween('treg', [$startDate, $endDate])
            ->get();

        $selectedRangeParam = $request->input('selectedRange', 'Today');
        $startDateParam = $request->input('startDate', null);
        $endDateParam = $request->input('endDate', null);

        return view('company.reports.vatSalesReport', compact('company', 'branches', 'transactions', 'branchId', 'dateParam', 'selectedRangeParam', 'startDateParam', 'endDateParam'));
    }

    public function xReadingReport(Request $request)
    {
        $company = $request->attributes->get('company');

        $branches = $company->activeBranches;

        $branchId = $request->query('branch_id', $branches->first()->id);

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

        return view('company.reports.xReadingReport', compact('company', 'branches', 'cutoffs', 'branchId', 'dateParam', 'paymentTypes', 'discountTypes', 'selectedRangeParam', 'startDateParam', 'endDateParam'));
    }

    public function zReadingReport(Request $request)
    {
        $company = $request->attributes->get('company');

        $branches = $company->activeBranches;

        $branchId = $request->query('branch_id', $branches->first()->id);

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
            ->orderBy('reading_number')
            ->get();

        $selectedRangeParam = $request->input('selectedRange', 'Today');
        $startDateParam = $request->input('startDate', null);
        $endDateParam = $request->input('endDate', null);

        return view('company/reports/zReadingReport', compact('company', 'branches', 'branchId', 'dateParam', 'paymentTypes', 'discountTypes', 'endOfDays', 'selectedRangeParam', 'startDateParam', 'endDateParam'));
    }

    public function discountsReport(Request $request)
    {
        $company = $request->attributes->get('company');
        $branches = $company->activeBranches;

        $branchId = $request->input('branch_id', $branches->first()->id);

        $dateParam = $request->input('date_range', null);

        $startDate = Carbon::now()->format('Y-m-d 00:00:00');
        $endDate = Carbon::now()->format('Y-m-d 23:59:59');
        if ($dateParam) {
            list($startDate, $endDate) = explode(" - ", $dateParam);

            $startDate = Carbon::parse($startDate)->format('Y-m-d 00:00:00');
            $endDate = Carbon::parse($endDate)->format('Y-m-d 23:59:59');
        }

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
                'pos_machines.machine_number',
                'transactions.id as transaction_id'
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

        $startDateParam = $startDate;
        $endDateParam = $endDate;

        $selectedRangeParam = $this->determineSelectedRange($startDateParam, $endDateParam);

        return view('company.reports.discountsReport', compact('company', 'branches', 'branchId', 'dateParam', 'discountTypes', 'discounts', 'filterDiscountTypes', 'selectedRangeParam', 'startDateParam', 'endDateParam'));
    }

    public function itemSales(Request $request)
    {
        $transactionalDbName = config('database.connections.transactional_db.database');

        $company = $request->attributes->get('company');
        $branches = $company->activeBranches;

        $branchId = $request->input('branch_id', $branches->first()->id);

        $dateParam = $request->input('date_range', null);

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
                    AND orders.is_return = FALSE
                LEFT JOIN $transactionalDbName.discount_details ON orders.order_id = discount_details.order_id
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

        return view('company.reports.itemSales', compact('company', 'branches', 'branchId', 'dateParam', 'itemSales', 'selectedRangeParam', 'startDateParam', 'endDateParam'));
    }

    public function stockCard(Request $request)
    {
        $transactionalDbName = config('database.connections.transactional_db.database');
        $defaultDbName = config('database.connections.mysql.database');

        $company = $request->attributes->get('company');
        $branches = $company->activeBranches;

        $branchId = $request->input('branch_id', $branches->first()->id);
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
        if ($branchId && $productId) {
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
                    orders.gross - (orders.cost * orders.qty) as `profit`
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
                    AND product_disposals.created_at BETWEEN '$startDate' AND '$endDate'
                    AND product_disposals.branch_id = $branchId
            ";

            $disposals = DB::select($disposalQuery);
        }

        $selectedRangeParam = $request->input('selectedRange', 'Today');
        $startDateParam = $request->input('startDate', null);
        $endDateParam = $request->input('endDate', null);

        return view('company.reports.stockCard', compact(
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
        $branches = $company->activeBranches;

        $machineIds = [];
        $machines = [];
        foreach ($branches as $branch) {
            foreach ($branch->machines as $machine) {
                if ($machine->type == 'cashier') {
                    $machineIds[] = $machine->id;
                    $machines[$machine->id] = $machine;
                }
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

        $selectedRangeParam = $request->input('selectedRange', 'Today');
        $startDateParam = $request->input('startDate', null);
        $endDateParam = $request->input('endDate', null);

        return view('company.reports.auditTrail', compact(
            'company',
            'trails',
            'machines',
            'selectedRangeParam',
            'startDateParam',
            'endDateParam',
            'machineId'
        ));
    }

    public function birSalesSummaryReport(Request $request)
    {
        $company = $request->attributes->get('company');

        $branches = $company->activeBranches;

        $branchId = $request->query('branch_id', $branches->first()->id);

        $dateParam = $request->input('date_range', null);

        $startDate = Carbon::now()->format('Y-m-d 00:00:00');
        $endDate = Carbon::now()->format('Y-m-d 23:59:59');
        if ($dateParam) {
            list($startDate, $endDate) = explode(" - ", $dateParam);

            $startDate = Carbon::parse($startDate)->format('Y-m-d 00:00:00');
            $endDate = Carbon::parse($endDate)->format('Y-m-d 23:59:59');
        }

        if ($request->isMethod('post') && !$request->input('search')) {
            $branch = Branch::find($branchId);
            return Excel::download(new BirSalesSummaryReportExport($branchId, $startDate, $endDate), "$branch->name - SALES SUMMARY  REPORT - $startDate - $endDate.xlsx");
        }

        $endOfDays = EndOfDay::where('branch_id', $branchId)
            ->whereBetween('treg', [$startDate, $endDate])
            ->get();

        $selectedRangeParam = $request->input('selectedRange', 'Today');
        $startDateParam = $request->input('startDate', null);
        $endDateParam = $request->input('endDate', null);

        return view('company.reports.birSalesSummaryReport', compact(
            'endOfDays',
            'branches',
            'branchId',
            'selectedRangeParam',
            'startDateParam',
            'endDateParam',
        ));
    }

    public function birSeniorCitizenSalesReport(Request $request)
    {
        $company = $request->attributes->get('company');
        $branches = $company->activeBranches;
        $branchId = $request->query('branch_id', $branches->first()->id);
        $dateParam = $request->input('date_range', null);

        $startDate = Carbon::now()->format('Y-m-d 00:00:00');
        $endDate = Carbon::now()->format('Y-m-d 23:59:59');
        if ($dateParam) {
            list($startDate, $endDate) = explode(" - ", $dateParam);

            $startDate = Carbon::parse($startDate)->format('Y-m-d 00:00:00');
            $endDate = Carbon::parse($endDate)->format('Y-m-d 23:59:59');
        }

        if ($request->isMethod('post') && !$request->input('search')) {
            $branch = Branch::find($branchId);
            return Excel::download(new BirSeniorSalesReportExport($branchId, $startDate, $endDate), "$branch->name - Senior Citizen Sales Book Report - $startDate - $endDate.xlsx");
        }

        $discounts = Discount::where('discount_type_id', 4)
            ->where('branch_id', $branchId)
            ->where('is_void', false)
            ->whereBetween('treg', [$startDate, $endDate])
            ->get();

        $selectedRangeParam = $request->input('selectedRange', 'Today');
        $startDateParam = $request->input('startDate', null);
        $endDateParam = $request->input('endDate', null);

        return view('company.reports.birSeniorCitizenSalesReport', compact(
            'company',
            'branches',
            'branchId',
            'selectedRangeParam',
            'startDateParam',
            'endDateParam',
            'discounts'
        ));
    }

    public function birPwdSalesReport(Request $request)
    {
        $company = $request->attributes->get('company');
        $branches = $company->activeBranches;
        $branchId = $request->query('branch_id', $branches->first()->id);
        $dateParam = $request->input('date_range', null);

        $startDate = Carbon::now()->format('Y-m-d 00:00:00');
        $endDate = Carbon::now()->format('Y-m-d 23:59:59');
        if ($dateParam) {
            list($startDate, $endDate) = explode(" - ", $dateParam);

            $startDate = Carbon::parse($startDate)->format('Y-m-d 00:00:00');
            $endDate = Carbon::parse($endDate)->format('Y-m-d 23:59:59');
        }

        if ($request->isMethod('post') && !$request->input('search')) {
            $branch = Branch::find($branchId);
            return Excel::download(new BirPwdSalesReportExport($branchId, $startDate, $endDate), "$branch->name - Persons with Disability Sales Book Report - $startDate - $endDate.xlsx");
        }

        $discounts = Discount::where('discount_type_id', 5)
            ->where('branch_id', $branchId)
            ->where('is_void', false)
            ->whereBetween('treg', [$startDate, $endDate])
            ->get();

        $selectedRangeParam = $request->input('selectedRange', 'Today');
        $startDateParam = $request->input('startDate', null);
        $endDateParam = $request->input('endDate', null);

        return view('company.reports.birPwdSalesReport', compact(
            'company',
            'branches',
            'branchId',
            'selectedRangeParam',
            'startDateParam',
            'endDateParam',
            'discounts'
        ));
    }

    public function birNaacSalesReport(Request $request)
    {
        $company = $request->attributes->get('company');
        $branches = $company->activeBranches;
        $branchId = $request->query('branch_id', $branches->first()->id);
        $dateParam = $request->input('date_range', null);

        $startDate = Carbon::now()->format('Y-m-d 00:00:00');
        $endDate = Carbon::now()->format('Y-m-d 23:59:59');
        if ($dateParam) {
            list($startDate, $endDate) = explode(" - ", $dateParam);

            $startDate = Carbon::parse($startDate)->format('Y-m-d 00:00:00');
            $endDate = Carbon::parse($endDate)->format('Y-m-d 23:59:59');
        }

        if ($request->isMethod('post') && !$request->input('search')) {
            $branch = Branch::find($branchId);
            return Excel::download(new BirNaacSalesReportExport($branchId, $startDate, $endDate), "$branch->name - NAAC Sales Book Report - $startDate - $endDate.xlsx");
        }

        $discounts = Discount::where('discount_type_id', 29)
            ->where('branch_id', $branchId)
            ->where('is_void', false)
            ->whereBetween('treg', [$startDate, $endDate])
            ->get();

        $selectedRangeParam = $request->input('selectedRange', 'Today');
        $startDateParam = $request->input('startDate', null);
        $endDateParam = $request->input('endDate', null);

        return view('company.reports.birNaacSalesReport', compact(
            'company',
            'branches',
            'branchId',
            'selectedRangeParam',
            'startDateParam',
            'endDateParam',
            'discounts'
        ));
    }

    public function birSoloParentSalesReport(Request $request)
    {
        $discountTypeId = 11;

        $company = $request->attributes->get('company');
        $branches = $company->activeBranches;
        $branchId = $request->query('branch_id', $branches->first()->id);
        $dateParam = $request->input('date_range', null);

        $startDate = Carbon::now()->format('Y-m-d 00:00:00');
        $endDate = Carbon::now()->format('Y-m-d 23:59:59');
        if ($dateParam) {
            list($startDate, $endDate) = explode(" - ", $dateParam);

            $startDate = Carbon::parse($startDate)->format('Y-m-d 00:00:00');
            $endDate = Carbon::parse($endDate)->format('Y-m-d 23:59:59');
        }

        if ($request->isMethod('post') && !$request->input('search')) {
            $branch = Branch::find($branchId);
            return Excel::download(new BirSoloParentSalesReportExport($branchId, $startDate, $endDate), "$branch->name - Solo Parent Sales Book Report - $startDate - $endDate.xlsx");
        }

        $discounts = Discount::where('discount_type_id', $discountTypeId)
            ->where('branch_id', $branchId)
            ->where('is_void', false)
            ->whereBetween('treg', [$startDate, $endDate])
            ->get();

        $selectedRangeParam = $request->input('selectedRange', 'Today');
        $startDateParam = $request->input('startDate', null);
        $endDateParam = $request->input('endDate', null);

        return view('company.reports.birSoloParentSalesReport', compact(
            'company',
            'branches',
            'branchId',
            'selectedRangeParam',
            'startDateParam',
            'endDateParam',
            'discounts'
        ));
    } 
    
    public function categorySalesReport(Request $request)
    {
        $company = $request->attributes->get('company');
        $branches = $company->activeBranches;
        
        $branchId = $request->query('branch_id', $branches->first()->id);

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
            
            return Excel::download(
                new CategorySalesReportExport($branchId, $startDate, $endDate),
                'Category_Sales_Report_' . $branch->name . '_' . Carbon::parse($startDate)->format('Y-m-d') . '_' . Carbon::parse($endDate)->format('Y-m-d') . '.xlsx'
            );
        }

        // Get category sales data for the view using raw SQL
        $categorySalesQuery = "
            SELECT 
                isync.categories.name as category, 
                SUM(transactional_db.orders.qty) as quantity_sold,
                SUM(CASE WHEN transactional_db.orders.discount_amount > 0 THEN transactional_db.orders.discount_amount ELSE 0 END) as discount_sales,
                SUM(transactional_db.orders.gross) as regular_sales
            FROM transactional_db.transactions
            INNER JOIN transactional_db.orders ON transactions.transaction_id = orders.transaction_id
                AND transactions.branch_id = orders.branch_id
                AND transactions.pos_machine_id = orders.pos_machine_id
                AND orders.is_void = FALSE
                AND orders.is_completed = TRUE
                AND orders.is_back_out = FALSE
            INNER JOIN isync.products ON orders.product_id = products.id
            INNER JOIN isync.categories ON products.category_id = categories.id
            WHERE transactions.is_complete = TRUE
                AND transactions.branch_id = $branchId
                AND transactions.is_void = FALSE
                AND transactions.is_back_out = FALSE
                AND transactions.treg BETWEEN '$startDate' AND '$endDate'
            GROUP BY isync.categories.name
            ORDER BY regular_sales DESC
        ";

        $categoryData = collect(DB::select($categorySalesQuery));

        // Calculate totals
        $totalRegularSales = $categoryData->sum('regular_sales');
        $totalDiscountSales = $categoryData->sum('discount_sales');
        
        // Add percentage to each category
        $categoryData->transform(function($item) use ($totalRegularSales) {
            $item->percentage = $totalRegularSales > 0 ? round(($item->regular_sales / $totalRegularSales) * 100) : 0;
            return $item;
        });

        $startDateParam = Carbon::parse($startDate)->format('m/d/Y');
        $endDateParam = Carbon::parse($endDate)->format('m/d/Y');
        $selectedRangeParam = $request->input('selectedRange', 'Today');

        return view('company.reports.categorySales', compact(
            'company', 
            'branches', 
            'branchId',
            'categoryData',
            'totalRegularSales',
            'totalDiscountSales',
            'startDateParam',
            'endDateParam',
            'selectedRangeParam'
        ));
    }

    public function departmentSalesReport(Request $request)
    {
        $company = $request->attributes->get('company');
        $branches = $company->activeBranches;
        
        $branchId = $request->query('branch_id', $branches->first()->id);

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
            
            return Excel::download(
                new DepartmentSalesReportExport($branchId, $startDate, $endDate),
                'Department_Sales_Report_' . $branch->name . '_' . Carbon::parse($startDate)->format('Y-m-d') . '_' . Carbon::parse($endDate)->format('Y-m-d') . '.xlsx'
            );
        }

        // Get department sales data for the view using raw SQL
        $departmentSalesQuery = "
            SELECT 
                isync.departments.name as department, 
                SUM(transactional_db.orders.qty) as quantity_sold,
                SUM(transactional_db.orders.gross) as gross_sales,
                SUM(CASE WHEN transactional_db.orders.discount_amount > 0 THEN transactional_db.orders.discount_amount ELSE 0 END) as discount_sales,
                SUM(transactional_db.orders.gross - CASE WHEN transactional_db.orders.discount_amount > 0 THEN transactional_db.orders.discount_amount ELSE 0 END) as net_sales
            FROM transactional_db.transactions
            INNER JOIN transactional_db.orders ON transactions.transaction_id = orders.transaction_id
                AND transactions.branch_id = orders.branch_id
                AND transactions.pos_machine_id = orders.pos_machine_id
                AND orders.is_void = FALSE
                AND orders.is_completed = TRUE
                AND orders.is_back_out = FALSE
            INNER JOIN isync.products ON orders.product_id = products.id
            INNER JOIN isync.departments ON products.department_id = departments.id
            WHERE transactions.is_complete = TRUE
                AND transactions.branch_id = $branchId
                AND transactions.is_void = FALSE
                AND transactions.is_back_out = FALSE
                AND transactions.treg BETWEEN '$startDate' AND '$endDate'
            GROUP BY isync.departments.name
            ORDER BY gross_sales DESC
        ";

        $departmentData = collect(DB::select($departmentSalesQuery));

        // Calculate totals
        $totalGrossSales = $departmentData->sum('gross_sales');
        $totalDiscountSales = $departmentData->sum('discount_sales');
        $totalNetSales = $departmentData->sum('net_sales');
        
        // Add percentage to each department
        $departmentData->transform(function($item) use ($totalNetSales) {
            $item->percentage = $totalNetSales > 0 ? round(($item->net_sales / $totalNetSales) * 100) : 0;
            return $item;
        });

        $startDateParam = Carbon::parse($startDate)->format('m/d/Y');
        $endDateParam = Carbon::parse($endDate)->format('m/d/Y');
        $selectedRangeParam = $request->input('selectedRange', 'Today');

        return view('company.reports.departmentSales', compact(
            'company', 
            'branches', 
            'branchId',
            'departmentData',
            'totalGrossSales',
            'totalDiscountSales',
            'totalNetSales',
            'startDateParam',
            'endDateParam',
            'selectedRangeParam'
        ));
    }

    public function salesReturnReport(Request $request)
    {
        $company = $request->attributes->get('company');
        $branches = $company->activeBranches;
        
        $branchId = $request->query('branch_id', $branches->first()->id);

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
            
            return Excel::download(
                new SalesReturnReportExport($branchId, $startDate, $endDate),
                'Sales_Return_Report_' . $branch->name . '_' . Carbon::parse($startDate)->format('Y-m-d') . '_' . Carbon::parse($endDate)->format('Y-m-d') . '.xlsx'
            );
        }

        // Get sales return data for the view using raw SQL
        $salesReturnQuery = "
            SELECT 
                isync.products.name, 
                transactional_db.transactions.pos_machine_id as machine_number,
                SUM(ABS(transactional_db.orders.qty)) as quantity,
                SUM(ABS(transactional_db.orders.gross)) as total_amount
            FROM transactional_db.transactions
            INNER JOIN transactional_db.orders ON transactions.transaction_id = orders.transaction_id
                AND transactions.branch_id = orders.branch_id
                AND transactions.pos_machine_id = orders.pos_machine_id
                AND orders.is_void = FALSE
                AND orders.is_completed = TRUE
                AND orders.is_back_out = FALSE
                AND orders.qty < 0  -- This is the key filter for returns
            INNER JOIN isync.products ON orders.product_id = products.id
            WHERE transactions.is_complete = TRUE
                AND transactions.branch_id = $branchId
                AND transactions.is_void = FALSE
                AND transactions.is_back_out = FALSE
                AND transactions.treg BETWEEN '$startDate' AND '$endDate'
            GROUP BY isync.products.name, transactional_db.transactions.pos_machine_id
            ORDER BY total_amount DESC
        ";

        $returnData = collect(DB::select($salesReturnQuery));

        // Calculate total
        $totalReturnAmount = $returnData->sum('total_amount');
        
        $startDateParam = Carbon::parse($startDate)->format('m/d/Y');
        $endDateParam = Carbon::parse($endDate)->format('m/d/Y');
        $selectedRangeParam = $request->input('selectedRange', 'Today');

        return view('company.reports.salesReturn', compact(
            'company', 
            'branches', 
            'branchId',
            'returnData',
            'totalReturnAmount',
            'startDateParam',
            'endDateParam',
            'selectedRangeParam'
        ));
    }

    public function subCategorySalesReport(Request $request)
    {
        $company = $request->attributes->get('company');
        $branches = $company->activeBranches;
        
        $branchId = $request->query('branch_id', $branches->first()->id);

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
            
            return Excel::download(
                new SubCategorySalesReportExport($branchId, $startDate, $endDate),
                'Sub_Category_Sales_Report_' . $branch->name . '_' . Carbon::parse($startDate)->format('Y-m-d') . '_' . Carbon::parse($endDate)->format('Y-m-d') . '.xlsx'
            );
        }

        // Get subcategory sales data for the view using raw SQL
        $subCategorySalesQuery = "
            SELECT 
                isync.subcategories.name as subcategory, 
                SUM(transactional_db.orders.qty) as quantity_sold,
                SUM(CASE WHEN transactional_db.orders.discount_amount > 0 THEN transactional_db.orders.discount_amount ELSE 0 END) as discount_sales,
                SUM(transactional_db.orders.gross) as regular_sales
            FROM transactional_db.transactions
            INNER JOIN transactional_db.orders ON transactions.transaction_id = orders.transaction_id
                AND transactions.branch_id = orders.branch_id
                AND transactions.pos_machine_id = orders.pos_machine_id
                AND orders.is_void = FALSE
                AND orders.is_completed = TRUE
                AND orders.is_back_out = FALSE
                AND orders.qty > 0  -- Exclude returns
            INNER JOIN isync.products ON orders.product_id = products.id
            INNER JOIN isync.subcategories ON products.subcategory_id = subcategories.id
            WHERE transactions.is_complete = TRUE
                AND transactions.branch_id = $branchId
                AND transactions.is_void = FALSE
                AND transactions.is_back_out = FALSE
                AND transactions.treg BETWEEN '$startDate' AND '$endDate'
            GROUP BY isync.subcategories.name
            ORDER BY regular_sales DESC
        ";

        $subcategoryData = collect(DB::select($subCategorySalesQuery));

        // Calculate totals
        $totalRegularSales = $subcategoryData->sum('regular_sales');
        $totalDiscountSales = $subcategoryData->sum('discount_sales');
        
        // Add percentage to each subcategory
        $subcategoryData->transform(function($item) use ($totalRegularSales) {
            $item->percentage = $totalRegularSales > 0 ? round(($item->regular_sales / $totalRegularSales) * 100) : 0;
            return $item;
        });

        $startDateParam = Carbon::parse($startDate)->format('m/d/Y');
        $endDateParam = Carbon::parse($endDate)->format('m/d/Y');
        $selectedRangeParam = $request->input('selectedRange', 'Today');

        return view('company.reports.subCategorySales', compact(
            'company', 
            'branches', 
            'branchId',
            'subcategoryData',
            'totalRegularSales',
            'totalDiscountSales',
            'startDateParam',
            'endDateParam',
            'selectedRangeParam'
        ));
    }
    
    public function hourlySalesReport(Request $request)
    {
        $company = $request->attributes->get('company');
        $branches = $company->activeBranches;
        
        $branchId = $request->query('branch_id', $branches->first()->id);

        $dateParam = $request->input('date_range', null);

        $startDate = Carbon::now()->format('Y-m-d');
        $endDate = Carbon::now()->format('Y-m-d');
        if ($dateParam) {
            list($startDate, $endDate) = explode(" - ", $dateParam);

            $startDate = Carbon::parse($startDate)->format('Y-m-d');
            $endDate = Carbon::parse($endDate)->format('Y-m-d');
        }

        if ($request->isMethod('post')) {
            $branch = Branch::find($branchId);
            
            return Excel::download(
                new HourlySalesReportExport($branchId, $startDate, $endDate),
                'Hourly_Sales_Report_' . $branch->name . '_' . Carbon::parse($startDate)->format('Y-m-d') . '_' . Carbon::parse($endDate)->format('Y-m-d') . '.xlsx'
            );
        }

        // Get hourly sales data for the view using raw SQL
        $hourlySalesQuery = "
            SELECT 
                DATE(transactions.treg) as sale_date,
                HOUR(transactions.treg) as sale_hour,
                SUM(transactions.gross_sales) as total_sales
            FROM transactional_db.transactions
            WHERE transactions.is_complete = TRUE
                AND transactions.branch_id = $branchId
                AND transactions.is_void = FALSE
                AND transactions.is_back_out = FALSE
                AND transactions.treg BETWEEN '$startDate 00:00:00' AND '$endDate 23:59:59'
            GROUP BY DATE(transactions.treg), HOUR(transactions.treg)
            ORDER BY sale_date, sale_hour
        ";

        $salesData = collect(DB::select($hourlySalesQuery));
        
        // Organize data by date and hour
        $salesByHour = [];
        $timeSlots = [
            '00:00-01:00', '01:00-02:00', '02:00-3:00', '3:00-4:00', '4:00-5:00',
            '5:00-6:00', '6:00-7:00', '7:00-08:00', '8:00-9:00', '9:00-10:00',
            '10:00-11:00', '11:00-12:00', '12:00-13:00', '13:00-14:00', '14:00-15:00',
            '15:00-16:00', '16:00-17:00', '17:00-18:00', '18:00-19:00', '19:00-20:00',
            '20:00-21:00', '21:00-22:00', '22:00-23:00', '23:00-24:00'
        ];
        
        foreach ($salesData as $sale) {
            $salesByHour[$sale->sale_date][intval($sale->sale_hour)] = $sale->total_sales;
        }
        
        // Create date range for the view
        $period = \Carbon\CarbonPeriod::create($startDate, $endDate);
        $days = [];
        
        foreach ($period as $date) {
            $days[] = [
                'date' => $date->format('Y-m-d'),
                'formatted_date' => $date->format('M j, Y'),
                'day_name' => $date->format('l')
            ];
        }

        $startDateParam = Carbon::parse($startDate)->format('m/d/Y');
        $endDateParam = Carbon::parse($endDate)->format('m/d/Y');
        $selectedRangeParam = $request->input('selectedRange', 'Today');

        return view('company.reports.hourlySales', compact(
            'company', 
            'branches', 
            'branchId',
            'salesByHour',
            'timeSlots',
            'days',
            'startDateParam',
            'endDateParam',
            'selectedRangeParam'
        ));
    }
    
    public function hourlySalesSummaryReport(Request $request)
    {
        $company = $request->attributes->get('company');
        $branches = $company->activeBranches;
        
        $branchId = $request->query('branch_id', $branches->first()->id);
        $branch = Branch::find($branchId);
        $branchName = $branch->name;

        $dateParam = $request->input('date_range', null);

        $startDate = Carbon::now()->format('Y-m-d');
        $endDate = Carbon::now()->format('Y-m-d');
        if ($dateParam) {
            list($startDate, $endDate) = explode(" - ", $dateParam);

            $startDate = Carbon::parse($startDate)->format('Y-m-d');
            $endDate = Carbon::parse($endDate)->format('Y-m-d');
        }

        if ($request->isMethod('post')) {
            return Excel::download(
                new HourlySalesSummaryExport($branchId, $startDate, $endDate),
                'Hourly_Sales_Summary_' . $branch->name . '_' . Carbon::parse($startDate)->format('Y-m-d') . '_' . Carbon::parse($endDate)->format('Y-m-d') . '.xlsx'
            );
        }

        // Get hourly sales data
        $hourlySalesQuery = "
            SELECT 
                DATE(transactions.treg) as sale_date,
                HOUR(transactions.treg) as sale_hour,
                SUM(transactions.gross_sales) as total_sales
            FROM transactional_db.transactions
            WHERE transactions.is_complete = TRUE
                AND transactions.branch_id = $branchId
                AND transactions.is_void = FALSE
                AND transactions.is_back_out = FALSE
                AND transactions.treg BETWEEN '$startDate 00:00:00' AND '$endDate 23:59:59'
            GROUP BY DATE(transactions.treg), HOUR(transactions.treg)
            ORDER BY sale_date, sale_hour
        ";

        $salesData = collect(DB::select($hourlySalesQuery));
        
        // Get daily summary data
        $daySummaryQuery = "
            SELECT 
                DATE(t.treg) as sale_date,
                COUNT(*) as transactions,
                SUM(t.gross_sales) as gross_sales,
                SUM(t.net_sales) as net_sales,
                SUM(t.discount_amount) as discounts,
                SUM(t.vatable_sales) as vat_sales,
                SUM(t.vat_exempt_sales) as vat_exempt_sales,
                SUM(t.vat_amount) as vat_amount,
                SUM(CASE WHEN p.payment_type_name = 'cash' THEN p.amount ELSE 0 END) as cash_sales,
                SUM(CASE WHEN p.payment_type_name = 'card' THEN p.amount ELSE 0 END) as card_sales,
                SUM(CASE WHEN p.payment_type_name = 'mobile' THEN p.amount ELSE 0 END) as mobile_sales,
                SUM(CASE WHEN p.payment_type_name = 'ar' THEN p.amount ELSE 0 END) as ar_sales,
                SUM(CASE WHEN p.payment_type_name = 'online' THEN p.amount ELSE 0 END) as online_sales,
                SUM(t.total_unit_cost) as unit_cost,
                SUM(t.service_charge) as service_charge,
                SUM(t.net_sales - t.total_unit_cost) as gross_profit,
                CASE 
                    WHEN SUM(t.net_sales) > 0 THEN (SUM(t.net_sales - t.total_unit_cost) / SUM(t.net_sales)) * 100
                    ELSE 0
                END as gross_profit_percentage
            FROM transactional_db.transactions t
            LEFT JOIN transactional_db.payments p ON p.transaction_id = t.id
            WHERE t.is_complete = TRUE
                AND t.branch_id = $branchId
                AND t.is_void = FALSE
                AND t.is_back_out = FALSE
                AND t.treg BETWEEN '$startDate 00:00:00' AND '$endDate 23:59:59'
            GROUP BY DATE(t.treg)
            ORDER BY sale_date
        ";
        
        $summaryData = collect(DB::select($daySummaryQuery));
        
        // Organize data by date and hour
        $salesByHour = [];
        $daySummary = [];
        $totals = [
            'transactions' => 0,
            'gross_sales' => 0,
            'net_sales' => 0,
            'discounts' => 0,
            'vat_sales' => 0,
            'vat_exempt_sales' => 0,
            'vat_amount' => 0,
            'cash_sales' => 0,
            'card_sales' => 0,
            'mobile_sales' => 0,
            'ar_sales' => 0,
            'online_sales' => 0,
            'unit_cost' => 0,
            'service_charge' => 0,
            'gross_profit' => 0
        ];
        
        $hourlyTotals = array_fill(0, 24, 0);
        
        foreach ($salesData as $sale) {
            $salesByHour[$sale->sale_date][intval($sale->sale_hour)] = $sale->total_sales;
            $hourlyTotals[intval($sale->sale_hour)] += $sale->total_sales;
        }
        
        foreach ($summaryData as $summary) {
            $daySummary[$summary->sale_date] = [
                'transactions' => $summary->transactions,
                'gross_sales' => $summary->gross_sales,
                'net_sales' => $summary->net_sales,
                'discounts' => $summary->discounts,
                'vat_sales' => $summary->vat_sales,
                'vat_exempt_sales' => $summary->vat_exempt_sales,
                'vat_amount' => $summary->vat_amount,
                'cash_sales' => $summary->cash_sales,
                'card_sales' => $summary->card_sales,
                'mobile_sales' => $summary->mobile_sales,
                'ar_sales' => $summary->ar_sales,
                'online_sales' => $summary->online_sales,
                'unit_cost' => $summary->unit_cost,
                'service_charge' => $summary->service_charge,
                'gross_profit' => $summary->gross_profit,
                'gross_profit_percentage' => $summary->gross_profit_percentage
            ];
            
            // Calculate totals
            $totals['transactions'] += $summary->transactions;
            $totals['gross_sales'] += $summary->gross_sales;
            $totals['net_sales'] += $summary->net_sales;
            $totals['discounts'] += $summary->discounts;
            $totals['vat_sales'] += $summary->vat_sales;
            $totals['vat_exempt_sales'] += $summary->vat_exempt_sales;
            $totals['vat_amount'] += $summary->vat_amount;
            $totals['cash_sales'] += $summary->cash_sales;
            $totals['card_sales'] += $summary->card_sales;
            $totals['mobile_sales'] += $summary->mobile_sales;
            $totals['ar_sales'] += $summary->ar_sales;
            $totals['online_sales'] += $summary->online_sales;
            $totals['unit_cost'] += $summary->unit_cost;
            $totals['service_charge'] += $summary->service_charge;
            $totals['gross_profit'] += $summary->gross_profit;
        }
        
        // Calculate overall gross profit percentage
        if ($totals['net_sales'] > 0) {
            $totals['gross_profit_percentage'] = ($totals['gross_profit'] / $totals['net_sales']) * 100;
        } else {
            $totals['gross_profit_percentage'] = 0;
        }
        
        $timeSlots = [
            '00:00-01:00', '01:00-02:00', '02:00-3:00', '3:00-4:00', '4:00-5:00',
            '5:00-6:00', '6:00-7:00', '7:00-08:00', '8:00-9:00', '9:00-10:00',
            '10:00-11:00', '11:00-12:00', '12:00-13:00', '13:00-14:00', '14:00-15:00',
            '15:00-16:00', '16:00-17:00', '17:00-18:00', '18:00-19:00', '19:00-20:00',
            '20:00-21:00', '21:00-22:00', '22:00-23:00', '23:00-24:00'
        ];
        
        // Create date range for the view
        $period = \Carbon\CarbonPeriod::create($startDate, $endDate);
        $days = [];
        
        foreach ($period as $date) {
            $days[] = [
                'date' => $date->format('Y-m-d'),
                'formatted_date' => $date->format('M j, Y'),
                'day_name' => $date->format('l')
            ];
        }

        $startDateParam = Carbon::parse($startDate)->format('m/d/Y');
        $endDateParam = Carbon::parse($endDate)->format('m/d/Y');
        $selectedRangeParam = $request->input('selectedRange', 'Today');

        return view('company.reports.hourlySalesSummary', compact(
            'company', 
            'branches', 
            'branchId',
            'branchName',
            'salesByHour',
            'daySummary',
            'timeSlots',
            'days',
            'startDateParam',
            'endDateParam',
            'selectedRangeParam',
            'totals',
            'hourlyTotals'
        ));
    }

    public function hourlyTransactionReport(Request $request)
    {
        $company = $request->attributes->get('company');
        $branches = $company->activeBranches;
        
        $branchId = $request->query('branch_id', $branches->first()->id);

        $dateParam = $request->input('date_range', null);

        $startDate = Carbon::now()->format('Y-m-d');
        $endDate = Carbon::now()->format('Y-m-d');
        if ($dateParam) {
            list($startDate, $endDate) = explode(" - ", $dateParam);

            $startDate = Carbon::parse($startDate)->format('Y-m-d');
            $endDate = Carbon::parse($endDate)->format('Y-m-d');
        }

        if ($request->isMethod('post')) {
            $branch = Branch::find($branchId);
            
            return Excel::download(
                new HourlyTransactionReportExport($branchId, $startDate, $endDate),
                'Hourly_Transaction_Report_' . $branch->name . '_' . Carbon::parse($startDate)->format('Y-m-d') . '_' . Carbon::parse($endDate)->format('Y-m-d') . '.xlsx'
            );
        }

        // Get hourly transaction data for the view using raw SQL
        $hourlyTransactionQuery = "
            SELECT 
                DATE(transactions.treg) as transaction_date,
                HOUR(transactions.treg) as transaction_hour,
                COUNT(transactions.id) as transaction_count
            FROM transactional_db.transactions
            WHERE transactions.is_complete = TRUE
                AND transactions.branch_id = $branchId
                AND transactions.is_void = FALSE
                AND transactions.is_back_out = FALSE
                AND transactions.treg BETWEEN '$startDate 00:00:00' AND '$endDate 23:59:59'
            GROUP BY DATE(transactions.treg), HOUR(transactions.treg)
            ORDER BY transaction_date, transaction_hour
        ";

        $transactionsData = collect(DB::select($hourlyTransactionQuery));
        
        // Organize data by date and hour
        $transactionsByHour = [];
        $timeSlots = [
            '00:00-01:00', '01:00-02:00', '02:00-3:00', '3:00-4:00', '4:00-5:00',
            '5:00-6:00', '6:00-7:00', '7:00-08:00', '8:00-9:00', '9:00-10:00',
            '10:00-11:00', '11:00-12:00', '12:00-13:00', '13:00-14:00', '14:00-15:00',
            '15:00-16:00', '16:00-17:00', '17:00-18:00', '18:00-19:00', '19:00-20:00',
            '20:00-21:00', '21:00-22:00', '22:00-23:00', '23:00-24:00'
        ];
        
        foreach ($transactionsData as $transaction) {
            $transactionsByHour[$transaction->transaction_date][intval($transaction->transaction_hour)] = $transaction->transaction_count;
        }
        
        // Create date range for the view
        $period = \Carbon\CarbonPeriod::create($startDate, $endDate);
        $days = [];
        
        foreach ($period as $date) {
            $days[] = [
                'date' => $date->format('Y-m-d'),
                'formatted_date' => $date->format('M j, Y'),
                'day_name' => $date->format('l')
            ];
        }

        $startDateParam = Carbon::parse($startDate)->format('m/d/Y');
        $endDateParam = Carbon::parse($endDate)->format('m/d/Y');
        $selectedRangeParam = $request->input('selectedRange', 'Today');

        return view('company.reports.hourlyTransaction', compact(
            'company', 
            'branches', 
            'branchId',
            'transactionsByHour',
            'timeSlots',
            'days',
            'startDateParam',
            'endDateParam',
            'selectedRangeParam'
        ));
    }

    public function safekeepingReport(Request $request)
    {
        $company = $request->attributes->get('company');
        $branches = $company->activeBranches;
        
        $branchId = $request->query('branch_id', $branches->first()->id);

        $dateParam = $request->input('date_range', null);

        $startDate = Carbon::now()->format('Y-m-d');
        $endDate = Carbon::now()->format('Y-m-d');
        if ($dateParam) {
            list($startDate, $endDate) = explode(" - ", $dateParam);

            $startDate = Carbon::parse($startDate)->format('Y-m-d');
            $endDate = Carbon::parse($endDate)->format('Y-m-d');
        }

        if ($request->isMethod('post')) {
            $branch = Branch::find($branchId);
            
            return Excel::download(
                new SafekeepingReportExport($branchId, $startDate, $endDate),
                'Safekeeping_Report_' . $branch->name . '_' . Carbon::parse($startDate)->format('Y-m-d') . '_' . Carbon::parse($endDate)->format('Y-m-d') . '.xlsx'
            );
        }

        // Get safekeeping data for the view
        $safekeepingQuery = "
            SELECT 
                safekeepings.created_at as date,
                safekeepings.pos_machine_id as machine_number,
                safekeepings.amount as amount,
                safekeepings.cashier_name as cashier_name
            FROM transactional_db.safekeepings
            WHERE safekeepings.branch_id = $branchId
                AND safekeepings.created_at BETWEEN '$startDate 00:00:00' AND '$endDate 23:59:59'
            ORDER BY safekeepings.created_at ASC
        ";

        $safekeepingData = collect(DB::select($safekeepingQuery));
        
        // Calculate total
        $totalAmount = $safekeepingData->sum('amount');

        $startDateParam = Carbon::parse($startDate)->format('m/d/Y');
        $endDateParam = Carbon::parse($endDate)->format('m/d/Y');
        $selectedRangeParam = $request->input('selectedRange', 'Today');

        return view('company.reports.safekeeping', compact(
            'company', 
            'branches', 
            'branchId',
            'safekeepingData',
            'totalAmount',
            'startDateParam',
            'endDateParam',
            'endDateParam',
            'selectedRangeParam'
        ));
    }

    public function topPerformingProducts(Request $request)
    {
        $company = $request->attributes->get('company');
        $branches = $company->activeBranches;

        $branchId = $request->input('branch_id', $branches->first()->id);
        $branch = Branch::find($branchId);

        $dateParam = $request->input('date_range', null);

        $startDate = Carbon::now()->format('Y-m-d 00:00:00');
        $endDate = Carbon::now()->format('Y-m-d 23:59:59');
        if ($dateParam) {
            list($startDate, $endDate) = explode(" - ", $dateParam);

            $startDate = Carbon::parse($startDate)->format('Y-m-d 00:00:00');
            $endDate = Carbon::parse($endDate)->format('Y-m-d 23:59:59');
        }

        if ($request->isMethod('post')) {
            return Excel::download(
                new TopPerformingProductsExport($branchId, $startDate, $endDate),
                "Top Performing Products Report - $startDate to $endDate.xlsx"
            );
        }

        $query = "SELECT
                    products.name AS `description`,
                    products.sku,
                    departments.name AS `department`,
                    categories.name AS `category`,
                    subcategories.name AS `sub_category`,
                    SUM(transactional_db.orders.qty) AS `quantity_sold`,
                    0 AS `ar_unpaid_quantity`,
                    SUM(transactional_db.orders.qty * products.cost) AS `total_unit_cost`,
                    SUM(transactional_db.discount_details.discount_amount) AS `discount_sales`,
                    SUM(transactional_db.orders.total) AS `regular_sales`,
                    (SUM(transactional_db.orders.total) / (SELECT SUM(total) FROM transactional_db.orders WHERE branch_id = $branchId) * 100) AS `sales_percentage`
                FROM transactional_db.transactions
                INNER JOIN transactional_db.orders ON transactions.transaction_id = orders.transaction_id
                    AND transactions.branch_id = orders.branch_id
                    AND transactions.pos_machine_id = orders.pos_machine_id
                    AND orders.is_void = FALSE
                    AND orders.is_completed = TRUE
                    AND orders.is_back_out = FALSE
                    AND orders.is_return = FALSE
                LEFT JOIN transactional_db.discount_details ON orders.order_id = discount_details.order_id
                    AND orders.branch_id = discount_details.branch_id
                    AND orders.pos_machine_id = discount_details.pos_machine_id
                INNER JOIN isync.products ON orders.product_id = products.id
                INNER JOIN isync.departments ON products.department_id = departments.id
                LEFT JOIN isync.categories ON products.category_id = categories.id
                LEFT JOIN isync.subcategories ON products.subcategory_id = subcategories.id
                WHERE transactions.is_complete = TRUE
                    AND transactions.branch_id = $branchId
                    AND transactions.is_void = FALSE
                    AND transactions.is_back_out = FALSE
                    AND transactions.treg BETWEEN '$startDate' AND '$endDate'
                GROUP BY orders.product_id
                ORDER BY `regular_sales` DESC
                LIMIT 100";

        $topProducts = DB::select($query);

        // Convert the array of objects into a collection
        $topProducts = collect($topProducts);

        $selectedRangeParam = $request->input('selectedRange', 'Today');
        $startDateParam = $request->input('startDate', null);
        $endDateParam = $request->input('endDate', null);

        return view('company.reports.topPerformingProducts', compact(
            'company', 
            'branches', 
            'branch',
            'branchId', 
            'dateParam', 
            'topProducts', 
            'selectedRangeParam', 
            'startDateParam', 
            'endDateParam'
        ));
    }

    public function monthlySalesSummaryReport(Request $request)
    {
        $company = $request->attributes->get('company');
        $branches = $company->activeBranches;
        
        $branchId = $request->query('branch_id', $branches->first()->id);
        $branch = Branch::find($branchId);
        $branchName = $branch->name;

        $dateParam = $request->input('date_range', null);

        // Default to the last 12 months if no date range provided
        $endDate = Carbon::now()->format('Y-m-d');
        $startDate = Carbon::now()->subMonths(11)->startOfMonth()->format('Y-m-d');
        
        if ($dateParam) {
            list($startDate, $endDate) = explode(" - ", $dateParam);

            $startDate = Carbon::parse($startDate)->format('Y-m-d');
            $endDate = Carbon::parse($endDate)->format('Y-m-d');
        }

        if ($request->isMethod('post')) {
            return Excel::download(
                new MonthlySalesSummaryExport($branchId, $startDate, $endDate),
                'Monthly_Sales_Summary_' . $branch->name . '_' . Carbon::parse($startDate)->format('Y-m-d') . '_' . Carbon::parse($endDate)->format('Y-m-d') . '.xlsx'
            );
        }

        // Get monthly sales data
        $monthlySalesQuery = "
            SELECT 
                YEAR(t.treg) as year,
                MONTH(t.treg) as month,
                MAX(MONTHNAME(t.treg)) as month_name,
                COUNT(*) as transactions,
                SUM(t.gross_sales) as gross_sales,
                SUM(t.net_sales) as net_sales,
                SUM(t.discount_amount) as discounts,
                SUM(t.vatable_sales) as vat_sales,
                SUM(t.vat_exempt_sales) as vat_exempt_sales,
                SUM(t.vat_amount) as vat_amount,
                SUM(CASE WHEN p.payment_type_name = 'cash' THEN p.amount ELSE 0 END) as cash_sales,
                SUM(CASE WHEN p.payment_type_name = 'card' THEN p.amount ELSE 0 END) as card_sales,
                SUM(CASE WHEN p.payment_type_name = 'mobile' THEN p.amount ELSE 0 END) as mobile_sales,
                SUM(CASE WHEN p.payment_type_name = 'ar' THEN p.amount ELSE 0 END) as ar_sales,
                SUM(CASE WHEN p.payment_type_name = 'online' THEN p.amount ELSE 0 END) as online_sales,
                SUM(t.total_unit_cost) as unit_cost,
                SUM(t.service_charge) as service_charge,
                SUM(t.net_sales - t.total_unit_cost) as gross_profit,
                CASE 
                    WHEN SUM(t.net_sales) > 0 THEN (SUM(t.net_sales - t.total_unit_cost) / SUM(t.net_sales)) * 100
                    ELSE 0
                END as gross_profit_percentage
            FROM transactional_db.transactions t
            LEFT JOIN transactional_db.payments p ON p.transaction_id = t.id
            WHERE t.is_complete = TRUE
                AND t.branch_id = {$branchId}
                AND t.is_void = FALSE
                AND t.is_back_out = FALSE
                AND t.treg BETWEEN '{$startDate} 00:00:00' AND '{$endDate} 23:59:59'
            GROUP BY YEAR(t.treg), MONTH(t.treg)
            ORDER BY YEAR(t.treg), MONTH(t.treg)
        ";
        
        $summaryData = collect(DB::select($monthlySalesQuery));
        
        // Process data and calculate totals
        $monthlySales = [];
        $totals = [
            'transactions' => 0,
            'gross_sales' => 0,
            'net_sales' => 0,
            'discounts' => 0,
            'vat_sales' => 0,
            'vat_exempt_sales' => 0,
            'vat_amount' => 0,
            'cash_sales' => 0,
            'card_sales' => 0,
            'mobile_sales' => 0,
            'ar_sales' => 0,
            'online_sales' => 0,
            'unit_cost' => 0,
            'service_charge' => 0,
            'gross_profit' => 0,
            'gross_profit_percentage' => 0
        ];
        
        foreach ($summaryData as $summary) {
            $monthlySales[] = [
                'year' => $summary->year,
                'month' => $summary->month,
                'month_name' => $summary->month_name,
                'transactions' => $summary->transactions,
                'gross_sales' => $summary->gross_sales,
                'net_sales' => $summary->net_sales,
                'discounts' => $summary->discounts,
                'vat_sales' => $summary->vat_sales,
                'vat_exempt_sales' => $summary->vat_exempt_sales,
                'vat_amount' => $summary->vat_amount,
                'cash_sales' => $summary->cash_sales,
                'card_sales' => $summary->card_sales,
                'mobile_sales' => $summary->mobile_sales,
                'ar_sales' => $summary->ar_sales,
                'online_sales' => $summary->online_sales,
                'unit_cost' => $summary->unit_cost,
                'service_charge' => $summary->service_charge,
                'gross_profit' => $summary->gross_profit,
                'gross_profit_percentage' => $summary->gross_profit_percentage
            ];
            
            // Calculate totals
            $totals['transactions'] += $summary->transactions;
            $totals['gross_sales'] += $summary->gross_sales;
            $totals['net_sales'] += $summary->net_sales;
            $totals['discounts'] += $summary->discounts;
            $totals['vat_sales'] += $summary->vat_sales;
            $totals['vat_exempt_sales'] += $summary->vat_exempt_sales;
            $totals['vat_amount'] += $summary->vat_amount;
            $totals['cash_sales'] += $summary->cash_sales;
            $totals['card_sales'] += $summary->card_sales;
            $totals['mobile_sales'] += $summary->mobile_sales;
            $totals['ar_sales'] += $summary->ar_sales;
            $totals['online_sales'] += $summary->online_sales;
            $totals['unit_cost'] += $summary->unit_cost;
            $totals['service_charge'] += $summary->service_charge;
            $totals['gross_profit'] += $summary->gross_profit;
        }
        
        // Calculate overall gross profit percentage
        if ($totals['net_sales'] > 0) {
            $totals['gross_profit_percentage'] = ($totals['gross_profit'] / $totals['net_sales']) * 100;
        } else {
            $totals['gross_profit_percentage'] = 0;
        }

        $startDateParam = Carbon::parse($startDate)->format('m/d/Y');
        $endDateParam = Carbon::parse($endDate)->format('m/d/Y');
        $selectedRangeParam = $request->input('selectedRange', 'Today');

        return view('company.reports.monthlySalesSummary', compact(
            'company', 
            'branches', 
            'branchId',
            'branchName',
            'monthlySales',
            'totals',
            'startDateParam',
            'endDateParam',
            'selectedRangeParam'
        ));
    }

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
}