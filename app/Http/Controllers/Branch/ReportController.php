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

use Carbon\Carbon;

use App\Exports\SalesTransactionReportExport;
use App\Exports\VoidTransactionsReportExport;
use App\Exports\VatSalesReportExport;
use App\Exports\XReadingReportExport;
use App\Exports\ZReadingReportExport;
use App\Exports\SalesInvoicesReportExport;
use App\Exports\DiscountsReportExport;

use Maatwebsite\Excel\Facades\Excel;

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

        $dateParam = $request->query('start_date', date('F Y'));

        $parsedDate = Carbon::parse($dateParam);

        $startDate = $parsedDate->startOfMonth()->format('Y-m-d H:i:s'); // 2024-02-01 00:00:00
        $endDate = $parsedDate->endOfMonth()->format('Y-m-d H:i:s');

        if ($request->isMethod('post')) {
            return Excel::download(new SalesInvoicesReportExport($branchId, $startDate, $endDate), 'sales transaction report.xlsx');
        }

        $transactions = Transaction::where('branch_id', $branchId)
            ->where('is_complete', true)
            ->whereBetween('treg', [$startDate, $endDate])
            ->get();

        return view('branch.reports.salesInvoicesReport', compact('transactions', 'branchId', 'dateParam'));
    }
    
    public function salesTransactionReport(Request $request)
    {
        $company = $request->attributes->get('company');
        $branch = $request->attributes->get('branch');

        $branchId = $branch->id;

        $dateParam = $request->query('start_date', date('F Y'));

        $parsedDate = Carbon::parse($dateParam);

        $startDate = $parsedDate->startOfMonth()->format('Y-m-d H:i:s'); // 2024-02-01 00:00:00
        $endDate = $parsedDate->endOfMonth()->format('Y-m-d H:i:s');

        if ($request->isMethod('post')) {
            return Excel::download(new SalesTransactionReportExport($branchId, $startDate, $endDate), 'sales transaction report.xlsx');
        }

        $transactions = Transaction::where('branch_id', $branchId)
            ->where('is_complete', true)
            ->where('is_void', false)
            ->whereBetween('treg', [$startDate, $endDate])
            ->get();

        return view('branch.reports.salesTransactionReport', compact('transactions', 'branchId', 'dateParam'));
    }

    public function voidTransactionsReport(Request $request)
    {
        $company = $request->attributes->get('company');

        $branch = $request->attributes->get('branch');

        $branchId = $branch->id;

        $dateParam = $request->query('start_date', date('F Y'));

        $parsedDate = Carbon::parse($dateParam);

        $startDate = $parsedDate->startOfMonth()->format('Y-m-d H:i:s'); // 2024-02-01 00:00:00
        $endDate = $parsedDate->endOfMonth()->format('Y-m-d H:i:s');

        if ($request->isMethod('post')) {
            return Excel::download(new VoidTransactionsReportExport($branchId, $startDate, $endDate), 'void transactions report.xlsx');
        }

        $transactions = Transaction::where([
                'branch_id' => $branchId,
                'is_void' => true,
            ])
            ->whereBetween('treg', [$startDate, $endDate])
            ->get();

        return view('branch.reports.voidTransactionsReport', compact('transactions', 'branchId', 'dateParam'));
    }

    public function vatSalesReport(Request $request)
    {
        $company = $request->attributes->get('company');

        $branch = $request->attributes->get('branch');

        $branchId = $branch->id;

        $dateParam = $request->query('start_date', date('F Y'));

        $parsedDate = Carbon::parse($dateParam);

        $startDate = $parsedDate->startOfMonth()->format('Y-m-d H:i:s'); // 2024-02-01 00:00:00
        $endDate = $parsedDate->endOfMonth()->format('Y-m-d H:i:s');

        if ($request->isMethod('post')) {
            return Excel::download(new VatSalesReportExport($branchId, $startDate, $endDate), 'vat sales report.xlsx');
        }

        $transactions = Transaction::where('branch_id', $branchId)
            ->where('is_complete', true)
            ->whereBetween('treg', [$startDate, $endDate])
            ->get();

        return view('branch.reports.vatSalesReport', compact('transactions', 'branchId', 'dateParam'));
    }

    public function xReadingReport(Request $request)
    {
        $company = $request->attributes->get('company');

        $branch = $request->attributes->get('branch');

        $branchId = $branch->id;

        $dateParam = $request->query('start_date', date('F Y'));

        $parsedDate = Carbon::parse($dateParam);

        $startDate = $parsedDate->startOfMonth()->format('Y-m-d H:i:s'); // 2024-02-01 00:00:00
        $endDate = $parsedDate->endOfMonth()->format('Y-m-d H:i:s');

        if ($request->isMethod('post')) {
            return Excel::download(new XReadingReportExport($branchId, $startDate, $endDate), 'X Reading Report.xlsx');
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

        return view('branch.reports.xReadingReport', compact('cutoffs', 'branchId', 'dateParam', 'paymentTypes', 'discountTypes'));
    }

    public function zReadingReport(Request $request)
    {
        $company = $request->attributes->get('company');

        $branch = $request->attributes->get('branch');

        $branchId = $branch->id;

        $dateParam = $request->query('start_date', date('F Y'));

        $parsedDate = Carbon::parse($dateParam);

        $startDate = $parsedDate->startOfMonth()->format('Y-m-d H:i:s'); // 2024-02-01 00:00:00
        $endDate = $parsedDate->endOfMonth()->format('Y-m-d H:i:s');

        if ($request->isMethod('post')) {
            return Excel::download(new ZReadingReportExport($branchId, $startDate, $endDate), 'Z Reading Report.xlsx');
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

        return view('branch.reports.zReadingReport', compact('branchId', 'dateParam', 'paymentTypes', 'discountTypes', 'endOfDays'));
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
            return Excel::download(new DiscountsReportExport($branchId, $startDate, $endDate, $filterDiscountTypes), 'Discounts Report.xlsx');
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

        return view('branch.reports.discountsReport', compact('company', 'branchId', 'dateParam', 'discountTypes', 'discounts', 'filterDiscountTypes'));
    }
}
