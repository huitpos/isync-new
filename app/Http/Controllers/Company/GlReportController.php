<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Services\Gl\GlReportService;
use Illuminate\Http\Request;

class GlReportController extends Controller
{
    public function __construct(private GlReportService $reports)
    {
    }

    public function ledger(Request $request, string $companySlug)
    {
        $company = $request->attributes->get('company');
        $from = $request->input('from', now()->startOfMonth()->toDateString());
        $to = $request->input('to', now()->toDateString());
        $branchId = $request->input('branch_id') ? (int) $request->input('branch_id') : null;
        $accountId = $request->input('account_id') ? (int) $request->input('account_id') : null;
        $ledger = null;

        if ($accountId) {
            $ledger = $this->reports->ledger($company->id, $accountId, $from, $to, $branchId);
        }

        return view('company.accounting.reports.ledger', [
            'company' => $company,
            'accounts' => $this->accounts($company->id),
            'branches' => $company->branches()->orderBy('name')->get(),
            'from' => $from,
            'to' => $to,
            'branchId' => $branchId,
            'accountId' => $accountId,
            'ledger' => $ledger,
        ]);
    }

    public function trialBalance(Request $request, string $companySlug)
    {
        $company = $request->attributes->get('company');
        $asOf = $request->input('as_of', now()->toDateString());
        $branchId = $request->input('branch_id') ? (int) $request->input('branch_id') : null;
        $rows = $this->reports->trialBalance($company->id, $asOf, $branchId);

        return view('company.accounting.reports.trial-balance', [
            'company' => $company,
            'branches' => $company->branches()->orderBy('name')->get(),
            'asOf' => $asOf,
            'branchId' => $branchId,
            'rows' => $rows,
            'debitTotal' => round($rows->sum('debit_balance'), 4),
            'creditTotal' => round($rows->sum('credit_balance'), 4),
        ]);
    }

    public function incomeStatement(Request $request, string $companySlug)
    {
        $company = $request->attributes->get('company');
        $from = $request->input('from', now()->startOfMonth()->toDateString());
        $to = $request->input('to', now()->toDateString());
        $branchId = $request->input('branch_id') ? (int) $request->input('branch_id') : null;

        return view('company.accounting.reports.income-statement', [
            'company' => $company,
            'branches' => $company->branches()->orderBy('name')->get(),
            'from' => $from,
            'to' => $to,
            'branchId' => $branchId,
            'report' => $this->reports->incomeStatement($company->id, $from, $to, $branchId),
        ]);
    }

    public function balanceSheet(Request $request, string $companySlug)
    {
        $company = $request->attributes->get('company');
        $asOf = $request->input('as_of', now()->toDateString());
        $branchId = $request->input('branch_id') ? (int) $request->input('branch_id') : null;

        return view('company.accounting.reports.balance-sheet', [
            'company' => $company,
            'branches' => $company->branches()->orderBy('name')->get(),
            'asOf' => $asOf,
            'branchId' => $branchId,
            'report' => $this->reports->balanceSheet($company->id, $asOf, $branchId),
        ]);
    }

    private function accounts(int $companyId)
    {
        return Account::where('company_id', $companyId)->orderBy('code')->get();
    }
}
