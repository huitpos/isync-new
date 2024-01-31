<?php

namespace App\Repositories;

use App\Models\Branch;
use App\Models\Transaction;
use Illuminate\Support\Collection;

use App\Repositories\Interfaces\BranchRepositoryInterface;

class BranchRepository implements BranchRepositoryInterface
{
    public function all(): Collection
    {
        return Branch::all();
    }

    public function get($parameters = []): ?Collection
    {
        return Branch::where($parameters)->get();
    }

    public function find(String $id): ?Branch
    {
        return Branch::find($id);
    }

    public function create(array $data): Branch
    {
        $branch = Branch::create($data);
        return $branch;
    }

    public function update(String $id, array $data): Bool
    {
        $branch = Branch::findOrFail($id);
        return $branch->update($data);
    }

    public function delete(String $id): Bool
    {
        $branch = Branch::findOrFail($id);
        return $branch->delete();
    }

    public function getTransactionAmount(String $id): Float
    {
        $amount = Transaction::where('branch_id', $id)->sum('net_sales');

        return $amount;
    }

    public function getTransactionCostAmount(String $id): Float
    {
        $amount = Transaction::where('branch_id', $id)->sum('total_unit_cost');

        return $amount;
    }

    public function getTransactionCount(String $id): Int
    {
        $amount = Transaction::where('branch_id', $id)->count();

        return $amount;
    }

    public function getCompletedTransaction(String $id): ?Collection
    {
        $transactions = Transaction::where('branch_id', $id)
            ->where('is_complete', true)
            ->get();

        return $transactions;
    }
}
