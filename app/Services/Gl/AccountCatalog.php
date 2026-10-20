<?php

namespace App\Services\Gl;

use App\Models\Account;
use App\Models\AccountMapping;
use App\Models\PaymentTypeAccount;
use Illuminate\Validation\ValidationException;

class AccountCatalog
{
    public const KEYS = [
        'accounts_receivable' => 'Accounts receivable',
        'inventory' => 'Inventory',
        'input_vat' => 'Input VAT',
        'unbilled_receipts' => 'Unbilled receipts',
        'accounts_payable' => 'Accounts payable',
        'output_vat' => 'Output VAT',
        'sales_vatable' => 'Sales - vatable',
        'sales_vat_exempt' => 'Sales - VAT exempt',
        'sales_discount' => 'Sales discount',
        'cogs' => 'COGS',
        'purchase_price_variance' => 'Purchase price variance',
        'retained_earnings' => 'Owner equity',
    ];

    public function enabled(int $companyId): bool
    {
        return Account::where('company_id', $companyId)->exists();
    }

    public function id(int $companyId, string $key): int
    {
        $accountId = AccountMapping::where('company_id', $companyId)
            ->where('key', $key)
            ->value('account_id');

        if (!$accountId) {
            $label = self::KEYS[$key] ?? $key;
            throw ValidationException::withMessages([
                'account' => "Map the {$label} account before posting.",
            ]);
        }

        return (int) $accountId;
    }

    public function paymentAccountId(int $companyId, int $paymentTypeId, ?string $paymentTypeName = null): int
    {
        $accountId = PaymentTypeAccount::where('company_id', $companyId)
            ->where('payment_type_id', $paymentTypeId)
            ->value('account_id');

        if (!$accountId) {
            $name = $paymentTypeName ?: ('payment type '.$paymentTypeId);
            throw ValidationException::withMessages([
                'account' => "Map {$name} to an account before posting sales.",
            ]);
        }

        return (int) $accountId;
    }
}
