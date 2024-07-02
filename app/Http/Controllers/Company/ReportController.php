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

    public function xReadingReport(Request $request)
    {
        $asdf = [41,103,108,109,110,111,112,113,116,117,118,121,125,126,128,129,135,138,140,141,142,143,144,145,146,147,148,149,151,152,155,156,157,158,159,160,161,162,163,164,166,167,168,171,172,173,175,176,177,178];

        foreach ($asdf as $a) {
            echo "(SELECT 
            SUM(IF(((udhs.has_trait = 0 AND br.score < 0)
                        OR (udhs.has_trait = 1 AND br.score > 0)),
                    1,
                    0)) AS satisfied_count
        FROM
            user_details
                JOIN
            user_detail_has_traits udhs ON user_details.id = udhs.user_detail_id
                JOIN
            baseline_results br ON br.trait_id = udhs.trait_id
        WHERE
            br.baseline_id = $a
                AND udhs.user_detail_id = UserDetails.id) AS `baseline_$a`,";
        }
        die();
        $company = $request->attributes->get('company');

        if ($request->isMethod('post')) {
            $startDate = $request->input('start_date');
            $endDate = $request->input('end_date');
            $branchId = $request->branch_id;

            return Excel::download(new XReadingReportExport($branchId, $startDate, $endDate), 'X Reading Report.xlsx');
        }

        return view('company.reports.xReadingReport', compact('company'));
    }
}