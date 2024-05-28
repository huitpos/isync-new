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

        if ($request->isMethod('post')) {
            $startDate = $request->input('start_date');
            $endDate = $request->input('end_date');
            $branchId = $request->branch_id;

            return Excel::download(new SalesTransactionReportExport($branchId, $startDate, $endDate), 'sales transaction report.xlsx');
        }

        return view('company.reports.salesTransactionReport', compact('company'));
    }

    public function voidTransactionsReport(Request $request)
    {
        $company = $request->attributes->get('company');

        if ($request->isMethod('post')) {
            $startDate = $request->input('start_date');
            $endDate = $request->input('end_date');
            $branchId = $request->branch_id;

            return Excel::download(new VoidTransactionsReportExport($branchId, $startDate, $endDate), 'void transactions report.xlsx');
        }

        return view('company.reports.voidTransactionsReport', compact('company'));
    }

    public function vatSalesReport(Request $request)
    {
        $company = $request->attributes->get('company');

        if ($request->isMethod('post')) {
            $startDate = $request->input('start_date');
            $endDate = $request->input('end_date');
            $branchId = $request->branch_id;

            return Excel::download(new VatSalesReportExport($branchId, $startDate, $endDate), 'vat sales report.xlsx');
        }

        return view('company.reports.vatSalesReport', compact('company'));
    }
}