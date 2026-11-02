<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Support\Facades\DB;

trait AccountReceivableQueries
{
    private function assertChargeAccountBelongsToCompany(int $companyId, int $customerId): void
    {
        $mainDb = config('database.connections.mysql.database');

        $exists = DB::table($mainDb . '.charge_accounts')
            ->where('id', $customerId)
            ->where('company_id', $companyId)
            ->exists();

        if (!$exists) {
            abort(404);
        }
    }

    private function accountReceivableSummaryQuery(int $companyId, array $branchIds, ?int $customerId = null)
    {
        return $this->accountReceivableBaseQuery($companyId, $branchIds, $customerId)
            ->select([
                'transactions.charge_account_id',
                'charge_accounts.name',
                'charge_accounts.address',
                DB::raw('SUM(transactions.gross_sales) AS total_sales'),
                DB::raw('SUM(CASE WHEN transactions.is_account_receivable_redeem = TRUE THEN transactions.gross_sales ELSE 0 END) AS redeemed_sales'),
                DB::raw('SUM(CASE WHEN transactions.is_account_receivable_redeem = FALSE THEN transactions.gross_sales ELSE 0 END) AS not_redeemed_sales'),
            ])
            ->groupBy(
                'transactions.charge_account_id',
                'charge_accounts.name',
                'charge_accounts.address'
            );
    }

    private function accountReceivableTotals(int $companyId, array $branchIds, ?int $customerId = null)
    {
        return $this->accountReceivableBaseQuery($companyId, $branchIds, $customerId)
            ->selectRaw('
                COALESCE(SUM(transactions.gross_sales), 0) AS total_sales,
                COALESCE(SUM(CASE WHEN transactions.is_account_receivable_redeem = TRUE THEN transactions.gross_sales ELSE 0 END), 0) AS redeemed_sales,
                COALESCE(SUM(CASE WHEN transactions.is_account_receivable_redeem = FALSE THEN transactions.gross_sales ELSE 0 END), 0) AS not_redeemed_sales
            ')
            ->first();
    }

    private function accountReceivableTransactionsQuery(array $branchIds, int $customerId, bool $includeBranch = false)
    {
        $transactionDb = config('database.connections.transactional_db.database');
        $mainDb = config('database.connections.mysql.database');

        $query = DB::table($transactionDb . '.transactions as transactions')
            ->join($transactionDb . '.orders as orders', function ($join) {
                $join->on('transactions.transaction_id', '=', 'orders.transaction_id')
                    ->on('orders.branch_id', '=', 'transactions.branch_id')
                    ->on('transactions.pos_machine_id', '=', 'orders.pos_machine_id');
            })
            ->leftJoin($mainDb . '.unit_of_measurements as unit_of_measurements', 'orders.unit_id', '=', 'unit_of_measurements.id')
            ->where('transactions.is_account_receivable', true)
            ->where('transactions.is_void', false)
            ->where('transactions.is_back_out', false)
            ->where('transactions.is_complete', true)
            ->whereIn('transactions.branch_id', $branchIds)
            ->where('transactions.charge_account_id', $customerId)
            ->select([
                'transactions.id',
                'transactions.receipt_number',
                'transactions.completed_at',
                'orders.description as item_description',
                'orders.qty',
                'unit_of_measurements.name as uom',
                'orders.gross',
                'orders.discount_amount',
                'transactions.cashier_name',
            ])
            ->orderByDesc('transactions.completed_at')
            ->orderByDesc('transactions.id');

        if ($includeBranch) {
            $query->leftJoin($mainDb . '.branches as branches', 'transactions.branch_id', '=', 'branches.id')
                ->addSelect('branches.name as branch_name');
        }

        return $query;
    }

    private function accountReceivableBaseQuery(int $companyId, array $branchIds, ?int $customerId = null)
    {
        $transactionDb = config('database.connections.transactional_db.database');
        $mainDb = config('database.connections.mysql.database');

        $query = DB::table($transactionDb . '.transactions as transactions')
            ->join($mainDb . '.charge_accounts as charge_accounts', 'transactions.charge_account_id', '=', 'charge_accounts.id')
            ->where('charge_accounts.company_id', $companyId)
            ->where('transactions.is_account_receivable', true)
            ->where('transactions.is_void', false)
            ->where('transactions.is_back_out', false)
            ->where('transactions.is_complete', true)
            ->whereIn('transactions.branch_id', $branchIds);

        if ($customerId) {
            $query->where('transactions.charge_account_id', $customerId);
        }

        return $query;
    }
}
