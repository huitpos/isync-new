<?php

namespace App\Repositories;

use App\Models\Company;
use App\Models\Transaction;
use Illuminate\Support\Collection;

use Illuminate\Support\Facades\DB;

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

    public function getTransactionNetSales(String $id, String $startDate = '', String $endDate = '', String $branchId = null): Float
    {
        $company = Company::findOrFail($id);

        $amount = Transaction::whereIn('branch_id', $company->branches->pluck('id')->toArray())
            ->when($branchId, function ($query, $branchId) {
                return $query->where('branch_id', $branchId);
            })
            ->where('is_complete', true)
            ->where('is_void', false)
            ->where('is_back_out', false)
            ->whereBetween('completed_at', [$startDate, $endDate])
            ->select(
                DB::raw('SUM(net_sales - discount_amount) as total_sales')
            )
            ->get();

        return $amount[0]->total_sales;
    }

    public function getTransactionGrossSales(String $id, String $startDate = '', String $endDate = '', String $branchId = null): Float
    {
        $company = Company::findOrFail($id);

        $amount = Transaction::whereIn('branch_id', $company->branches->pluck('id')->toArray())
            ->when($branchId, function ($query, $branchId) {
                return $query->where('branch_id', $branchId);
            })
            ->where('is_complete', true)
            ->where('is_void', false)
            ->where('is_back_out', false)
            ->whereBetween('completed_at', [$startDate, $endDate])
            ->sum('gross_sales');

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

    public function getTransactionCount(String $id, String $startDate = '', String $endDate = '', String $branchId = null): Int
    {
        $company = Company::findOrFail($id);

        $amount = Transaction::whereIn('branch_id', $company->branches->pluck('id')->toArray())
            ->when($branchId, function ($query, $branchId) {
                return $query->where('branch_id', $branchId);
            })
            ->where('is_complete', true)
            ->where('is_void', false)
            ->where('is_back_out', false)
            ->whereBetween('completed_at', [$startDate, $endDate])
            ->count();

        return $amount;
    }

    public function getCompletedTransactions(String $id): ?Collection
    {
        $company = Company::findOrFail($id);

        $transactions = Transaction::whereIn('branch_id', $company->branches->pluck('id')->toArray())
            ->where('is_complete', true)
            ->orderBy('receipt_number', 'desc')
            ->limit(100)
            ->get();

        return $transactions;
    }

    public function getPendingTransactions(String $id): ?Collection
    {
        $company = Company::findOrFail($id);

        $transactions = Transaction::whereIn('branch_id', $company->branches->pluck('id')->toArray())
            ->where('is_complete', false)
            ->orderBy('treg', 'desc')
            ->limit(100)
            ->get();

        return $transactions;
    }

}
