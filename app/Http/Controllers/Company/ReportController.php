<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

use App\Models\Transaction;

use App\DataTables\Company\Reports\TransactionsDataTable;
use App\Exports\TestExport;
use App\Exports\SalesTransactionReportExport;
use App\Exports\VoidTransactionsReportExport;
use App\Exports\VatSalesReportExport;
use App\Exports\XReadingReportExport;
use App\Exports\ZReadingReportExport;

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

        $transaction = Transaction::where(['id' => $id])
            ->first();

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
            return Excel::download(new SalesTransactionReportExport($branchId, $startDate, $endDate), 'sales transaction report.xlsx');
        }

        $transactions = Transaction::where('branch_id', $branchId)
            ->where('is_complete', true)
            ->whereBetween('treg', [$startDate, $endDate])
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
            return Excel::download(new VoidTransactionsReportExport($branchId, $startDate, $endDate), 'void transactions report.xlsx');
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
            return Excel::download(new VatSalesReportExport($branchId, $startDate, $endDate), 'vat sales report.xlsx');
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

        if ($request->isMethod('post')) {
            $startDate = $request->input('start_date');
            $endDate = $request->input('end_date');
            $branchId = $request->branch_id;

            return Excel::download(new XReadingReportExport($branchId, $startDate, $endDate), 'X Reading Report.xlsx');
        }

        return view('company.reports.xReadingReport', compact('company'));
    }

    public function zReadingReport(Request $request)
    {
        $company = $request->attributes->get('company');

        if ($request->isMethod('post')) {
            $startDate = $request->input('start_date');
            $endDate = $request->input('end_date');
            $branchId = $request->branch_id;

            return Excel::download(new ZReadingReportExport($branchId, $startDate, $endDate), 'Z Reading Report.xlsx');
        }

        return view('company.reports.zReadingReport', compact('company'));
    }
}