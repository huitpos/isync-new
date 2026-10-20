<?php

namespace App\Services\Gl;

use App\Models\Branch;
use App\Models\JournalEntry;
use App\Models\Payment;
use App\Models\PaymentTypeAccount;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;

class PosSalesJournal
{
    public function __construct(
        private AccountCatalog $catalog,
        private PosSalesAssembler $assembler,
        private JournalPoster $poster
    ) {
    }

    public function sourceKey(int $branchId, string $date): string
    {
        return $branchId.':'.$date;
    }

    public function existing(int $companyId, int $branchId, string $date): ?JournalEntry
    {
        return JournalEntry::where('company_id', $companyId)
            ->where('source_type', 'pos_sales')
            ->where('source_key', $this->sourceKey($branchId, $date))
            ->where('status', 'posted')
            ->first();
    }

    public function lines(Branch $branch, string $date): array
    {
        $companyId = (int) $branch->company_id;
        $transactions = $this->transactions($branch->id, $date);
        if (!$transactions) {
            throw ValidationException::withMessages([
                'date' => 'No completed sales for this date.',
            ]);
        }
        $payments = $this->payments($branch->id, $transactions);

        return $this->assembler->build($branch->id, $transactions, $payments, $this->accounts($companyId, $payments));
    }

    public function post(Branch $branch, string $date): JournalEntry
    {
        $companyId = (int) $branch->company_id;
        $lines = $this->lines($branch, $date);

        return $this->poster->post($companyId, [
            'entry_date' => $date,
            'source_type' => 'pos_sales',
            'source_key' => $this->sourceKey($branch->id, $date),
            'description' => 'POS sales '.$branch->name.' '.$date,
        ], $lines);
    }

    private function accounts(int $companyId, array $payments): array
    {
        $typeIds = collect($payments)
            ->reject(fn ($payment) => !empty($payment['is_void']) || !empty($payment['is_account_receivable']) || !empty($payment['is_advance_payment']))
            ->pluck('payment_type_id')
            ->unique()
            ->filter()
            ->all();

        $mapped = PaymentTypeAccount::where('company_id', $companyId)
            ->whereIn('payment_type_id', $typeIds)
            ->pluck('account_id', 'payment_type_id')
            ->all();

        return [
            'sales_vatable' => $this->catalog->id($companyId, 'sales_vatable'),
            'sales_vat_exempt' => $this->catalog->id($companyId, 'sales_vat_exempt'),
            'output_vat' => $this->catalog->id($companyId, 'output_vat'),
            'sales_discount' => $this->catalog->id($companyId, 'sales_discount'),
            'accounts_receivable' => $this->catalog->id($companyId, 'accounts_receivable'),
            'payment_types' => $mapped,
        ];
    }

    private function transactions(int $branchId, string $date): array
    {
        $day = Carbon::parse($date);

        return Transaction::query()
            ->where('branch_id', $branchId)
            ->where('is_void', false)
            ->where('is_complete', true)
            ->where('is_back_out', false)
            ->where('completed_at', '>=', $day->copy()->startOfDay())
            ->where('completed_at', '<=', $day->copy()->endOfDay())
            ->get()
            ->map(fn (Transaction $transaction) => $transaction->toArray())
            ->all();
    }

    private function payments(int $branchId, array $transactions): array
    {
        if (!$transactions) {
            return [];
        }

        $transactionIds = collect($transactions)->pluck('transaction_id')->unique()->all();

        return Payment::query()
            ->where('branch_id', $branchId)
            ->whereIn('transaction_id', $transactionIds)
            ->get()
            ->map(fn (Payment $payment) => $payment->toArray())
            ->all();
    }
}
