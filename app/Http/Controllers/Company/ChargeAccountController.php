<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Repositories\Interfaces\ChargeAccountRepositoryInterface;

use App\DataTables\Company\ChargeAccountsDataTable;

class ChargeAccountController extends Controller
{
    protected $chargeAccountRepository;

    public function __construct(ChargeAccountRepositoryInterface $chargeAccountRepository)
    {
        $this->chargeAccountRepository = $chargeAccountRepository;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, ChargeAccountsDataTable $dataTable)
    {
        $company = $request->attributes->get('company');
        $permissions = $request->attributes->get('permissionNames');

        return $dataTable->with([
                'company_id' => $company->id,
                'permissions' => $permissions
            ])
            ->render('company.chargeAccounts.index', compact('company', 'permissions'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $company = $request->attributes->get('company');

        return view('company.chargeAccounts.create', [
            'company' => $company,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'credit_limit' => 'required|numeric',
            'address' => 'required',
            'contact_number' => 'required',
            'email' => 'required|email',
            'status' => 'required',
        ]);

        $company = $request->attributes->get('company');

        $postData = $request->all();
        $postData['company_id'] = $company->id;

        if ($this->chargeAccountRepository->create($postData)) {
            return redirect()->route('company.charge-accounts.index', ['companySlug' => $company->slug])->with('success', 'Data has been stored successfully!');
        }

        return redirect()->back()->with('error', 'Data failed to store!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, string $companySlug, string $id)
    {
        $company = $request->attributes->get('company');
        $chargeAccount = $this->chargeAccountRepository->find($id);

        return view('company.chargeAccounts.show', [
            'company' => $company,
            'chargeAccount' => $chargeAccount,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request, string $companySlug, string $id)
    {
        $company = $request->attributes->get('company');
        $chargeAccount = $this->chargeAccountRepository->find($id);

        return view('company.chargeAccounts.edit', [
            'company' => $company,
            'chargeAccount' => $chargeAccount,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $companySlug, string $id)
    {
        $request->validate([
            'name' => 'required',
            'credit_limit' => 'required|numeric',
            'address' => 'required',
            'contact_number' => 'required',
            'email' => 'required|email',
            'status' => 'required',
        ]);

        $company = $request->attributes->get('company');

        $postData = $request->all();
        $postData['company_id'] = $company->id;

        if ($this->chargeAccountRepository->update($id, $postData)) {
            return redirect()->route('company.charge-accounts.index', ['companySlug' => $company->slug])->with('success', 'Data has been updated successfully!');
        }

        return redirect()->back()->with('error', 'Data failed to update!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
