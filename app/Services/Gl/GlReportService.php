<?php

namespace App\Services\Gl;

use App\Models\Account;
use App\Models\JournalLine;
use Illuminate\Support\Collection;

class GlReportService
{
    public function lines(int $companyId, ?string $from, ?string $to, ?int $branchId, ?int $accountId = null): Collection
    {
        $query = JournalLine::query()
            ->select([
                'journal_lines.*',
                'journal_entries.entry_date',
                'journal_entries.number',
                'journal_entries.description',
                'journal_entries.status',
                'accounts.code',
                'accounts.name as account_name',
                'accounts.type',
            ])
            ->join('journal_entries', 'journal_entries.id', '=', 'journal_lines.journal_entry_id')
            ->join('accounts', 'accounts.id', '=', 'journal_lines.account_id')
            ->where('journal_entries.company_id', $companyId)
            ->whereIn('journal_entries.status', ['posted', 'void'])
            ->orderBy('journal_entries.entry_date')
            ->orderBy('journal_entries.id')
            ->orderBy('journal_lines.line_no');

        if ($from) {
            $query->whereDate('journal_entries.entry_date', '>=', $from);
        }
        if ($to) {
            $query->whereDate('journal_entries.entry_date', '<=', $to);
        }
        if ($branchId) {
            $query->where('journal_lines.branch_id', $branchId);
        }
        if ($accountId) {
            $query->where('journal_lines.account_id', $accountId);
        }

        return $query->get();
    }

    public function trialBalance(int $companyId, string $asOf, ?int $branchId): Collection
    {
        return $this->balances($companyId, null, $asOf, $branchId)
            ->map(function ($row) {
                $net = round($row->debit - $row->credit, 4);
                $row->debit_balance = $net > 0 ? $net : 0;
                $row->credit_balance = $net < 0 ? abs($net) : 0;

                return $row;
            })
            ->filter(fn ($row) => abs($row->debit_balance) >= 0.0001 || abs($row->credit_balance) >= 0.0001)
            ->values();
    }

    public function incomeStatement(int $companyId, string $from, string $to, ?int $branchId): array
    {
        $rows = $this->balances($companyId, $from, $to, $branchId)
            ->whereIn('type', ['income', 'expense'])
            ->map(function ($row) {
                $row->amount = $row->type === 'income'
                    ? round($row->credit - $row->debit, 4)
                    : round($row->debit - $row->credit, 4);

                return $row;
            })
            ->filter(fn ($row) => abs($row->amount) >= 0.0001)
            ->values();

        $income = round($rows->where('type', 'income')->sum('amount'), 4);
        $expense = round($rows->where('type', 'expense')->sum('amount'), 4);

        return [
            'rows' => $rows,
            'income' => $income,
            'expense' => $expense,
            'net' => round($income - $expense, 4),
        ];
    }

    public function balanceSheet(int $companyId, string $asOf, ?int $branchId): array
    {
        $rows = $this->balances($companyId, null, $asOf, $branchId);
        $sections = $rows->whereIn('type', ['asset', 'liability', 'equity'])->map(function ($row) {
            if ($row->type === 'asset') {
                $row->amount = round($row->debit - $row->credit, 4);
            } else {
                $row->amount = round($row->credit - $row->debit, 4);
            }

            return $row;
        })->filter(fn ($row) => abs($row->amount) >= 0.0001)->values();

        $earnings = $this->incomeStatement($companyId, '1970-01-01', $asOf, $branchId)['net'];

        return [
            'rows' => $sections,
            'assets' => round($sections->where('type', 'asset')->sum('amount'), 4),
            'liabilities' => round($sections->where('type', 'liability')->sum('amount'), 4),
            'equity' => round($sections->where('type', 'equity')->sum('amount'), 4),
            'earnings' => $earnings,
        ];
    }

    public function ledger(int $companyId, int $accountId, string $from, string $to, ?int $branchId): array
    {
        $account = Account::where('company_id', $companyId)->findOrFail($accountId);
        $openingRows = $this->lines($companyId, null, date('Y-m-d', strtotime($from.' -1 day')), $branchId, $accountId);
        $opening = $this->signed($account->type, $openingRows->sum('debit'), $openingRows->sum('credit'));
        $rows = $this->lines($companyId, $from, $to, $branchId, $accountId);
        $running = $opening;
        $movement = $rows->map(function ($row) use ($account, &$running) {
            $running = round($running + $this->signed($account->type, $row->debit, $row->credit), 4);
            $row->balance = $running;

            return $row;
        });

        return [
            'account' => $account,
            'opening' => $opening,
            'rows' => $movement,
            'closing' => $running,
        ];
    }

    private function balances(int $companyId, ?string $from, ?string $to, ?int $branchId): Collection
    {
        return $this->lines($companyId, $from, $to, $branchId)
            ->groupBy('account_id')
            ->map(function (Collection $group) {
                $first = $group->first();

                return (object) [
                    'account_id' => $first->account_id,
                    'code' => $first->code,
                    'account_name' => $first->account_name,
                    'type' => $first->type,
                    'debit' => round($group->sum('debit'), 4),
                    'credit' => round($group->sum('credit'), 4),
                ];
            })
            ->sortBy('code')
            ->values();
    }

    private function signed(string $type, $debit, $credit): float
    {
        if (in_array($type, ['asset', 'expense'], true)) {
            return round((float) $debit - (float) $credit, 4);
        }

        return round((float) $credit - (float) $debit, 4);
    }
}
