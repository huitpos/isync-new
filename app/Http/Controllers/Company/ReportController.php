<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Transaction;

use App\DataTables\Company\Reports\TransactionsDataTable;

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
}