<?php

namespace App\Repositories;

use App\Models\Company;
use App\Models\Transaction;
use Illuminate\Support\Collection;

use App\Repositories\Interfaces\CompanyRepositoryInterface;

class CompanyRepository implements CompanyRepositoryInterface
{
    public function all(): Collection
    {
        return Company::all();
    }

    public function get($parameters = []): ?Collection
    {
        return Company::where($parameters)->get();
    }

    public function find(String $id): ?Company
    {
        return Company::find($id);
    }

    public function create(array $data): Company
    {
        $company = Company::create($data);
        return $company;
    }

    public function update(String $id, array $data): Bool
    {
        $company = Company::findOrFail($id);
        return $company->update($data);
    }

    public function delete(String $id): Bool
    {
        $company = Company::findOrFail($id);
        return $company->delete();
    }

    public function getTransactionAmount(String $id): Float
    {
        $company = Company::findOrFail($id);

        $amount = Transaction::whereIn('branch_id', $company->branches->pluck('id')->toArray())
            ->where('is_complete', true)
            ->sum('net_sales');

        return $amount;
    }

    public function getTransactionCostAmount(String $id): Float
    {
        $company = Company::findOrFail($id);

        $amount = Transaction::whereIn('branch_id', $company->branches->pluck('id')->toArray())
            ->where('is_complete', true)
            ->sum('total_unit_cost');

        return $amount;
    }

    public function getTransactionCount(String $id): Int
    {
        $company = Company::findOrFail($id);

        $amount = Transaction::whereIn('branch_id', $company->branches->pluck('id')->toArray())
            ->where('is_complete', true)
            ->count();

        return $amount;
    }

    public function getCompletedTransactions(String $id): ?Collection
    {
        $company = Company::findOrFail($id);

        $transactions = Transaction::whereIn('branch_id', $company->branches->pluck('id')->toArray())
            ->where('is_complete', true)
            ->orderBy('id', 'desc')
            ->get();

        return $transactions;
    }

    public function getPendingTransactions(String $id): ?Collection
    {
        $company = Company::findOrFail($id);

        $transactions = Transaction::whereIn('branch_id', $company->branches->pluck('id')->toArray())
            ->where('is_complete', false)
            ->orderBy('id', 'desc')
            ->get();

        return $transactions;
    }

}
