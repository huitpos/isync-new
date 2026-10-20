<?php

namespace App\Services\Gl;

use App\Models\Account;
use App\Models\AccountMapping;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class StarterChart
{
    public function load(int $companyId): void
    {
        if (Account::where('company_id', $companyId)->exists()) {
            throw ValidationException::withMessages([
                'accounts' => 'A chart of accounts already exists for this company.',
            ]);
        }

        $rows = [
            ['1000', 'Cash on hand', 'asset', null],
            ['1100', 'Accounts receivable', 'asset', 'accounts_receivable'],
            ['1200', 'Inventory', 'asset', 'inventory'],
            ['1300', 'Input VAT', 'asset', 'input_vat'],
            ['1400', 'Unbilled receipts', 'asset', 'unbilled_receipts'],
            ['2000', 'Accounts payable', 'liability', 'accounts_payable'],
            ['2100', 'Output VAT', 'liability', 'output_vat'],
            ['3000', 'Owner equity', 'equity', 'retained_earnings'],
            ['4000', 'Sales - vatable', 'income', 'sales_vatable'],
            ['4100', 'Sales - VAT exempt', 'income', 'sales_vat_exempt'],
            ['4200', 'Sales discount', 'income', 'sales_discount'],
            ['5000', 'COGS', 'expense', 'cogs'],
            ['5100', 'Purchase price variance', 'expense', 'purchase_price_variance'],
        ];

        DB::transaction(function () use ($companyId, $rows) {
            foreach ($rows as [$code, $name, $type, $key]) {
                $account = Account::create([
                    'company_id' => $companyId,
                    'code' => $code,
                    'name' => $name,
                    'type' => $type,
                    'is_postable' => true,
                    'is_active' => true,
                ]);

                if ($key) {
                    AccountMapping::create([
                        'company_id' => $companyId,
                        'key' => $key,
                        'account_id' => $account->id,
                    ]);
                }
            }
        });
    }
}
