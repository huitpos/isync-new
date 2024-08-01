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

use App\DataTables\Company\Reports\TransactionsDataTable;
use App\Exports\TestExport;
use App\Exports\SalesTransactionReportExport;
use App\Exports\VoidTransactionsReportExport;
use App\Exports\VatSalesReportExport;
use App\Exports\XReadingReportExport;
use App\Exports\ZReadingReportExport;
use App\Exports\SalesInvoicesReportExport;
use App\Exports\DiscountsReportExport;

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

        $dateParam = $request->query('start_date', date('F Y'));

        $parsedDate = Carbon::parse($dateParam);

        $startDate = $parsedDate->startOfMonth()->format('Y-m-d H:i:s'); // 2024-02-01 00:00:00
        $endDate = $parsedDate->endOfMonth()->format('Y-m-d H:i:s');

        if ($request->isMethod('post')) {
            $branch = Branch::find($branchId);
            return Excel::download(new SalesInvoicesReportExport($branchId, $startDate, $endDate), "$branch->name - Sales Invoices Report - $dateParam.xlsx");
        }

        $transactions = Transaction::where('branch_id', $branchId)
            ->where('is_complete', true)
            ->whereBetween('completed_at', [$startDate, $endDate])
            ->get();

        return view('company.reports.salesInvoicesReport', compact('company', 'branches', 'transactions', 'branchId', 'dateParam'));
    }

    public function salesTransactionReport(Request $request)
    {
        $company = $request->attributes->get('company');

        $branches = $company->activeBranches;

        $branchId = $request->query('branch_id', $branches->first()->id);

        $dateParam = $request->query('start_date', date('F Y'));

        $parsedDate = Carbon::parse($dateParam);

        $startDate = $parsedDate->startOfMonth()->format('Y-m-d H:i:s'); // 2024-02-01 00:00:00
        $endDate = $parsedDate->endOfMonth()->format('Y-m-d H:i:s');

        if ($request->isMethod('post')) {
            $branch = Branch::find($branchId);
            return Excel::download(new SalesTransactionReportExport($branchId, $startDate, $endDate), "$branch->name - Sales Transaction Report - $dateParam.xlsx");
        }

        $transactions = Transaction::where('branch_id', $branchId)
            ->where('is_complete', true)
            ->where('is_void', false)
            ->whereBetween('completed_at', [$startDate, $endDate])
            ->get();

        return view('company.reports.salesTransactionReport', compact('company', 'branches', 'transactions', 'branchId', 'dateParam'));
    }

    public function voidTransactionsReport(Request $request)
    {
        $company = $request->attributes->get('company');

        $branches = $company->activeBranches;

        $branchId = $request->query('branch_id', $branches->first()->id);

        $dateParam = $request->query('start_date', date('F Y'));

        $parsedDate = Carbon::parse($dateParam);

        $startDate = $parsedDate->startOfMonth()->format('Y-m-d H:i:s'); // 2024-02-01 00:00:00
        $endDate = $parsedDate->endOfMonth()->format('Y-m-d H:i:s');

        if ($request->isMethod('post')) {
            $branch = Branch::find($branchId);
            return Excel::download(new VoidTransactionsReportExport($branchId, $startDate, $endDate), "$branch->name - Void Transactions Report - $dateParam.xlsx");
        }

        $transactions = Transaction::where([
                'branch_id' => $branchId,
                'is_void' => true,
            ])
            ->whereBetween('treg', [$startDate, $endDate])
            ->get();

        return view('company.reports.voidTransactionsReport', compact('company', 'branches', 'transactions', 'branchId', 'dateParam'));
    }

    public function vatSalesReport(Request $request)
    {
        $company = $request->attributes->get('company');

        $branches = $company->activeBranches;

        $branchId = $request->query('branch_id', $branches->first()->id);

        $dateParam = $request->query('start_date', date('F Y'));

        $parsedDate = Carbon::parse($dateParam);

        $startDate = $parsedDate->startOfMonth()->format('Y-m-d H:i:s'); // 2024-02-01 00:00:00
        $endDate = $parsedDate->endOfMonth()->format('Y-m-d H:i:s');

        if ($request->isMethod('post')) {
            $branch = Branch::find($branchId);
            return Excel::download(new VatSalesReportExport($branchId, $startDate, $endDate), "$branch->name - Vat Sales Report - $dateParam.xlsx");
        }

        $transactions = Transaction::where('branch_id', $branchId)
            ->where('is_complete', true)
            ->whereBetween('treg', [$startDate, $endDate])
            ->get();

        return view('company.reports.vatSalesReport', compact('company', 'branches', 'transactions', 'branchId', 'dateParam'));
    }

    public function xReadingReport(Request $request)
    {
        $company = $request->attributes->get('company');

        $branches = $company->activeBranches;

        $branchId = $request->query('branch_id', $branches->first()->id);

        $dateParam = $request->query('start_date', date('F Y'));

        $parsedDate = Carbon::parse($dateParam);

        $startDate = $parsedDate->startOfMonth()->format('Y-m-d H:i:s'); // 2024-02-01 00:00:00
        $endDate = $parsedDate->endOfMonth()->format('Y-m-d H:i:s');

        if ($request->isMethod('post')) {
            $branch = Branch::find($branchId);
            return Excel::download(new XReadingReportExport($branchId, $startDate, $endDate), "$branch->name - X Reading Report - $dateParam.xlsx");
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

        return view('company.reports.xReadingReport', compact('company', 'branches', 'cutoffs', 'branchId', 'dateParam', 'paymentTypes', 'discountTypes'));
    }

    public function zReadingReport(Request $request)
    {
        $company = $request->attributes->get('company');

        $branches = $company->activeBranches;

        $branchId = $request->query('branch_id', $branches->first()->id);

        $dateParam = $request->query('start_date', date('F Y'));

        $parsedDate = Carbon::parse($dateParam);

        $startDate = $parsedDate->startOfMonth()->format('Y-m-d H:i:s'); // 2024-02-01 00:00:00
        $endDate = $parsedDate->endOfMonth()->format('Y-m-d H:i:s');

        if ($request->isMethod('post')) {
            $branch = Branch::find($branchId);
            return Excel::download(new ZReadingReportExport($branchId, $startDate, $endDate), "$branch->name - Z Reading Report - $dateParam.xlsx");
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

        return view('company.reports.zReadingReport', compact('company', 'branches', 'branchId', 'dateParam', 'paymentTypes', 'discountTypes', 'endOfDays'));
    }

    public function discountsReport(Request $request)
    {
        $company = $request->attributes->get('company');
        $branches = $company->activeBranches;

        $branchId = $request->input('branch_id', $branches->first()->id);

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
            return Excel::download(new DiscountsReportExport($branchId, $startDate, $endDate, $filterDiscountTypes), "$branch->name - Discounts Report - $dateParam.xlsx");
        }

        $discounts = Discount::select([
                'transactions.completed_at as date',
                'transactions.receipt_number',
                'transactions.gross_sales',
                'discounts.discount_name',
                'discounts.discount_amount',
                'discounts.discount_id',
                'discounts.pos_machine_id',
                'discounts.branch_id',
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

        return view('company.reports.discountsReport', compact('company', 'branches', 'branchId', 'dateParam', 'discountTypes', 'discounts', 'filterDiscountTypes'));
    }
}