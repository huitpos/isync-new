<?php

namespace App\Services\Gl;

use Illuminate\Validation\ValidationException;

class PosSalesAssembler
{
    public function build(int $branchId, array $transactions, array $payments, array $accounts): array
    {
        if (!$transactions) {
            throw ValidationException::withMessages([
                'date' => 'No completed sales for this date.',
            ]);
        }

        $paymentsByKey = [];
        foreach ($payments as $payment) {
            if (!empty($payment['is_void']) || !empty($payment['is_advance_payment'])) {
                continue;
            }
            $key = $payment['branch_id'].'|'.$payment['pos_machine_id'].'|'.$payment['transaction_id'];
            $paymentsByKey[$key][] = $payment;
        }

        $nets = [];
        $plug = 0.0;

        foreach ($transactions as $transaction) {
            $key = $transaction['branch_id'].'|'.$transaction['pos_machine_id'].'|'.$transaction['transaction_id'];
            $txPayments = $paymentsByKey[$key] ?? [];

            if (!empty($transaction['is_account_receivable_redeem'])) {
                $this->addCollection($nets, $accounts, $txPayments, (float) ($transaction['change'] ?? 0));
                continue;
            }

            $this->addSale($nets, $accounts, $transaction, $txPayments);
        }

        $sum = round(array_sum($nets), 4);
        if (abs($sum) >= 0.0001) {
            $discountId = $accounts['sales_discount'];
            $nets[$discountId] = round(($nets[$discountId] ?? 0) - $sum, 4);
            $plug = $sum;
        }

        $lines = [];
        foreach ($nets as $accountId => $net) {
            $net = round($net, 4);
            if (abs($net) < 0.0001) {
                continue;
            }
            $memo = null;
            if ((int) $accountId === (int) $accounts['sales_discount'] && abs($plug) >= 0.0001) {
                $memo = 'Includes sales day balancing difference';
            }
            $lines[] = [
                'account_id' => (int) $accountId,
                'branch_id' => $branchId,
                'debit' => $net > 0 ? $net : 0,
                'credit' => $net < 0 ? abs($net) : 0,
                'memo' => $memo,
            ];
        }

        if (count($lines) < 2) {
            throw ValidationException::withMessages([
                'date' => 'This sales day has no amounts to post.',
            ]);
        }

        return $lines;
    }

    private function addSale(array &$nets, array $accounts, array $transaction, array $payments): void
    {
        $sign = $this->sign($transaction);
        [$tenders, $ar] = $this->splitPayments($accounts, $payments, (float) ($transaction['change'] ?? 0));

        $vatable = $sign * ((float) ($transaction['vatable_sales'] ?? 0) + (float) ($transaction['service_charge'] ?? 0));
        $exempt = $sign * ((float) ($transaction['vat_exempt_sales'] ?? 0) + (float) ($transaction['total_zero_rated_amount'] ?? 0));
        $vat = $sign * (float) ($transaction['vat_amount'] ?? 0);
        $discount = $sign * ((float) ($transaction['discount_amount'] ?? 0) + (float) ($transaction['vat_expense'] ?? 0));

        if (!empty($transaction['is_account_receivable']) && abs($ar) < 0.0001) {
            $due = round($vatable + $exempt + $vat - $discount - array_sum($tenders), 4);
            if ($due > 0) {
                $ar = $due;
            }
        }

        $this->credit($nets, $accounts['sales_vatable'], $vatable);
        $this->credit($nets, $accounts['sales_vat_exempt'], $exempt);
        $this->credit($nets, $accounts['output_vat'], $vat);
        $this->debit($nets, $accounts['sales_discount'], $discount);
        foreach ($tenders as $accountId => $amount) {
            $this->debit($nets, $accountId, $sign * $amount);
        }
        $this->debit($nets, $accounts['accounts_receivable'], $sign * $ar);
    }

    private function addCollection(array &$nets, array $accounts, array $payments, float $change): void
    {
        [$tenders] = $this->splitPayments($accounts, $payments, $change);
        $collected = round(array_sum($tenders), 4);
        foreach ($tenders as $accountId => $amount) {
            $this->debit($nets, $accountId, $amount);
        }
        $this->credit($nets, $accounts['accounts_receivable'], $collected);
    }

    private function splitPayments(array $accounts, array $payments, float $change): array
    {
        $tenders = [];
        $ar = 0.0;

        foreach ($payments as $payment) {
            $amount = round((float) ($payment['amount'] ?? 0), 4);
            if (!empty($payment['is_account_receivable'])) {
                $ar += $amount;
                continue;
            }

            $typeId = (int) $payment['payment_type_id'];
            if (!isset($accounts['payment_types'][$typeId])) {
                $name = $payment['payment_type_name'] ?? ('payment type '.$typeId);
                throw ValidationException::withMessages([
                    'account' => "Map {$name} to an account before posting sales.",
                ]);
            }
            $accountId = $accounts['payment_types'][$typeId];
            $tenders[$accountId] = round(($tenders[$accountId] ?? 0) + $amount, 4);
        }

        $remaining = round($change, 4);
        if ($remaining > 0 && $tenders) {
            arsort($tenders);
            foreach ($tenders as $accountId => $amount) {
                $take = min($amount, $remaining);
                $tenders[$accountId] = round($amount - $take, 4);
                $remaining = round($remaining - $take, 4);
                if ($remaining <= 0) {
                    break;
                }
            }
        }

        return [$tenders, round($ar, 4)];
    }

    private function sign(array $transaction): float
    {
        if (empty($transaction['is_return'])) {
            return 1;
        }

        $net = (float) ($transaction['net_sales'] ?? 0);
        $vatable = (float) ($transaction['vatable_sales'] ?? 0);

        return ($net >= 0 && $vatable >= 0) ? -1 : 1;
    }

    private function debit(array &$nets, int $accountId, float $amount): void
    {
        if (abs($amount) < 0.0001) {
            return;
        }
        $nets[$accountId] = round(($nets[$accountId] ?? 0) + $amount, 4);
    }

    private function credit(array &$nets, int $accountId, float $amount): void
    {
        $this->debit($nets, $accountId, -$amount);
    }
}
