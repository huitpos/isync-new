<?php

namespace App\DataTables\Reports\Concerns;

use Carbon\Carbon;

trait ReadsReportFilters
{
    protected function reportBranchId(): int
    {
        return (int) ($this->branch_id ?: request()->input('branch_id'));
    }

    protected function reportDates(): array
    {
        $dateParam = request()->input('date_range');
        if (is_string($dateParam) && str_contains($dateParam, ' - ')) {
            [$start, $end] = explode(' - ', $dateParam, 2);

            return [
                Carbon::parse(trim($start))->startOfDay()->format('Y-m-d H:i:s'),
                Carbon::parse(trim($end))->endOfDay()->format('Y-m-d H:i:s'),
            ];
        }

        $month = request()->input('start_date');
        if (is_string($month) && $month !== '') {
            $parsed = Carbon::parse($month);

            return [
                $parsed->copy()->startOfMonth()->format('Y-m-d H:i:s'),
                $parsed->copy()->endOfMonth()->format('Y-m-d H:i:s'),
            ];
        }

        return [
            now()->startOfDay()->format('Y-m-d H:i:s'),
            now()->endOfDay()->format('Y-m-d H:i:s'),
        ];
    }

    protected function money($value): string
    {
        return number_format((float) $value, 2);
    }

    protected function transactionUrl(int $transactionId): string
    {
        $area = $this->link_area ?? 'company';
        $params = [
            'companySlug' => $this->company_slug,
            'transactionId' => $transactionId,
        ];

        if ($area === 'branch') {
            $params['branchSlug'] = $this->branch_slug;
        }

        return route($area . '.reports.view-transaction', $params);
    }

    protected function transactionLink(object $transaction): string
    {
        $label = trim(($transaction->transaction_number ?? '') . ' ' . ($transaction->receipt_number ?? ''));

        return '<a href="' . e($this->transactionUrl((int) $transaction->id)) . '" target="_blank">' . e($label) . '</a>';
    }
}
