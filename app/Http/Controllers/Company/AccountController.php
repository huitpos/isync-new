<?php

namespace App\Http\Controllers\Company;

use App\DataTables\Company\AccountsDataTable;
use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Services\Gl\StarterChart;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class AccountController extends Controller
{
    public function index(Request $request, string $companySlug, AccountsDataTable $dataTable)
    {
        $company = $request->attributes->get('company');

        return $dataTable->with([
            'company_id' => $company->id,
            'company_slug' => $company->slug,
        ])->render('company.accounting.accounts.index', [
            'company' => $company,
            'hasAccounts' => Account::where('company_id', $company->id)->exists(),
        ]);
    }

    public function create(Request $request, string $companySlug)
    {
        $company = $request->attributes->get('company');

        return view('company.accounting.accounts.create', [
            'company' => $company,
            'parents' => $this->parents($company->id),
        ]);
    }

    public function store(Request $request, string $companySlug)
    {
        $company = $request->attributes->get('company');
        $data = $this->validated($request, $company->id);

        Account::create($data + ['company_id' => $company->id]);

        return redirect()
            ->route('company.accounts.index', ['companySlug' => $company->slug])
            ->with('success', 'Account created.');
    }

    public function edit(Request $request, string $companySlug, string $id)
    {
        $company = $request->attributes->get('company');
        $account = $this->find($company->id, $id);

        return view('company.accounting.accounts.edit', [
            'company' => $company,
            'account' => $account,
            'parents' => $this->parents($company->id, $account->id),
        ]);
    }

    public function update(Request $request, string $companySlug, string $id)
    {
        $company = $request->attributes->get('company');
        $account = $this->find($company->id, $id);
        $data = $this->validated($request, $company->id, $account->id);

        if ($data['type'] !== $account->type && $account->journalLines()->exists()) {
            throw ValidationException::withMessages([
                'type' => 'This account already has journal lines, so its type cannot change.',
            ]);
        }

        $account->update($data);

        return redirect()
            ->route('company.accounts.index', ['companySlug' => $company->slug])
            ->with('success', 'Account updated.');
    }

    public function starter(Request $request, string $companySlug, StarterChart $starter)
    {
        $company = $request->attributes->get('company');
        $starter->load($company->id);

        return redirect()
            ->route('company.accounts.index', ['companySlug' => $company->slug])
            ->with('success', 'Starter chart loaded. Map each payment type before posting sales.');
    }

    private function validated(Request $request, int $companyId, ?int $ignoreId = null): array
    {
        $data = $request->validate([
            'code' => [
                'required',
                'string',
                'max:20',
                Rule::unique('accounts', 'code')->where('company_id', $companyId)->ignore($ignoreId),
            ],
            'name' => 'required|string|max:255',
            'type' => 'required|in:asset,liability,equity,income,expense',
            'parent_id' => [
                'nullable',
                Rule::exists('accounts', 'id')->where('company_id', $companyId),
            ],
            'is_postable' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
        ]);

        if ($ignoreId && (int) ($data['parent_id'] ?? 0) === $ignoreId) {
            throw ValidationException::withMessages([
                'parent_id' => 'An account cannot be its own parent.',
            ]);
        }

        $data['is_postable'] = $request->boolean('is_postable');
        $data['is_active'] = $request->boolean('is_active');
        $data['parent_id'] = $data['parent_id'] ?: null;

        return $data;
    }

    private function parents(int $companyId, ?int $ignoreId = null)
    {
        return Account::where('company_id', $companyId)
            ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
            ->orderBy('code')
            ->get();
    }

    private function find(int $companyId, string $id): Account
    {
        return Account::where('company_id', $companyId)->findOrFail($id);
    }
}
