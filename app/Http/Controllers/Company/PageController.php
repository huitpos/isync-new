<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Repositories\Interfaces\CompanyRepositoryInterface;

class PageController extends Controller
{
    protected $companyRepository;

    public function __construct(CompanyRepositoryInterface $companyRepository) {
        $this->companyRepository = $companyRepository;
    }

    public function dashboard(Request $request)
    {
        $company = $request->attributes->get('company');

        $transactionAmount = $this->companyRepository->getTransactionAmount($company->id);
        $costAmount = $this->companyRepository->getTransactionCostAmount($company->id);
        $transactionCount = $this->companyRepository->getTransactionCount($company->id);
        $completedTransactions = $this->companyRepository->getCompletedTransactions($company->id);
        $pendingTransactions = $this->companyRepository->getPendingTransactions($company->id);

        return view('company.dashboard', [
            'company' => $company,
            'transactionAmount' => $transactionAmount,
            'costAmount' => $costAmount,
            'transactionCount' => $transactionCount,
            'completedTransactions' => $completedTransactions,
            'pendingTransactions' => $pendingTransactions,
        ]);
    }
}