<?php

namespace App\Http\Controllers\Branch;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $company = $request->attributes->get('company');
        $branch = $request->attributes->get('branch');

        $transactions = $branch->transactions()->paginate(50);

        return view('branch.transactions.index', [
            'company' => $company,
            'branch' => $branch,
            'transactions' => $transactions
        ]);
    }
}
