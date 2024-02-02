<?php

namespace App\Http\Controllers\Branch;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Repositories\Interfaces\BranchRepositoryInterface;

class PageController extends Controller
{
    protected $branchRepository;

    public function __construct(BranchRepositoryInterface $branchRepository) {
        $this->branchRepository = $branchRepository;
    }

    public function dashboard(Request $request)
    {
        $company = $request->attributes->get('company');
        $branch = $request->attributes->get('branch');

        $transactionAmount = $this->branchRepository->getTransactionAmount($branch->id);
        $costAmount = $this->branchRepository->getTransactionCostAmount($branch->id);
        $transactionCount = $this->branchRepository->getTransactionCount($branch->id);
        $completedTransactions = $this->branchRepository->getCompletedTransactions($branch->id);
        $pendingTransactions = $this->branchRepository->getPendingTransactions($branch->id);

        return view('branch.dashboard', compact([
            'company',
            'branch',
            'transactionAmount',
            'costAmount',
            'transactionCount',
            'completedTransactions',
            'pendingTransactions'
        ]));
    }
}
