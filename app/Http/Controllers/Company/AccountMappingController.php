<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\AccountMapping;
use App\Models\PaymentType;
use App\Models\PaymentTypeAccount;
use App\Services\Gl\AccountCatalog;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class AccountMappingController extends Controller
{
    public function edit(Request $request, string $companySlug)
    {
        $company = $request->attributes->get('company');
        $accounts = Account::where('company_id', $company->id)
            ->where('is_postable', true)
            ->where('is_active', true)
            ->orderBy('code')
            ->get();
        $mappings = AccountMapping::where('company_id', $company->id)->pluck('account_id', 'key');
        $paymentTypes = $this->paymentTypes($company->id);
        $paymentAccounts = PaymentTypeAccount::where('company_id', $company->id)
            ->whereIn('payment_type_id', $paymentTypes->pluck('id'))
            ->pluck('account_id', 'payment_type_id');

        return view('company.accounting.mappings.edit', [
            'company' => $company,
            'accounts' => $accounts,
            'mappings' => $mappings,
            'keys' => AccountCatalog::KEYS,
            'paymentTypes' => $paymentTypes,
            'paymentAccounts' => $paymentAccounts,
        ]);
    }

    public function update(Request $request, string $companySlug)
    {
        $company = $request->attributes->get('company');
        $accounts = Account::where('company_id', $company->id)
            ->where('is_postable', true)
            ->where('is_active', true)
            ->pluck('id');
        $paymentTypes = $this->paymentTypes($company->id);

        foreach (AccountCatalog::KEYS as $key => $label) {
            $accountId = $request->input('mappings.'.$key);
            if (!$accountId) {
                AccountMapping::where('company_id', $company->id)->where('key', $key)->delete();
                continue;
            }
            if (!$accounts->contains((int) $accountId)) {
                throw ValidationException::withMessages([
                    'mappings.'.$key => "Select a postable account for {$label}.",
                ]);
            }
            AccountMapping::updateOrCreate(
                ['company_id' => $company->id, 'key' => $key],
                ['account_id' => $accountId]
            );
        }

        foreach ($paymentTypes as $paymentType) {
            $accountId = $request->input('payment_types.'.$paymentType->id);
            if (!$accountId) {
                PaymentTypeAccount::where('company_id', $company->id)
                    ->where('payment_type_id', $paymentType->id)
                    ->delete();
                continue;
            }
            if (!$accounts->contains((int) $accountId)) {
                throw ValidationException::withMessages([
                    'payment_types.'.$paymentType->id => "Select a postable account for {$paymentType->name}.",
                ]);
            }
            PaymentTypeAccount::updateOrCreate(
                ['company_id' => $company->id, 'payment_type_id' => $paymentType->id],
                ['account_id' => $accountId]
            );
        }

        return redirect()
            ->route('company.account-mappings.edit', ['companySlug' => $company->slug])
            ->with('success', 'Account mapping saved.');
    }

    private function paymentTypes(int $companyId)
    {
        return PaymentType::query()
            ->where(function ($query) use ($companyId) {
                $query->where('company_id', $companyId)->orWhereNull('company_id');
            })
            ->orderBy('name')
            ->get();
    }
}
