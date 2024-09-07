<?php

namespace App\Http\Controllers\Branch;

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
        $branch = $request->attributes->get('branch');

        return $dataTable->with([
                'branch_id' => $branch->id,
                'company_slug' => $company->slug,
                'branch_slug' => $branch->slug,
            ])
            ->render('branch.chargeAccounts.index', compact('company', 'branch'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $company = $request->attributes->get('company');
        $branch = $request->attributes->get('branch');

        return view('branch.chargeAccounts.create', [
            'company' => $company,
            'branch' => $branch,
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
        $branch = $request->attributes->get('branch');

        $postData = $request->all();
        $postData['company_id'] = $company->id;
        $postData['branch_id'] = $branch->id;

        if ($this->chargeAccountRepository->create($postData)) {
            return redirect()->route('branch.charge-accounts.index', ['companySlug' => $company->slug, 'branchSlug' => $branch->slug])->with('success', 'Data has been stored successfully!');
        }

        return redirect()->back()->with('error', 'Data failed to store!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, string $companySlug, string $branchSlug, string $id)
    {
        $company = $request->attributes->get('company');
        $chargeAccount = $this->chargeAccountRepository->find($id);

        return view('branch.chargeAccounts.show', [
            'company' => $company,
            'chargeAccount' => $chargeAccount,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request, string $companySlug, string $branchSlug, string $id)
    {
        $company = $request->attributes->get('company');
        $branch = $request->attributes->get('branch');
        $chargeAccount = $this->chargeAccountRepository->find($id);

        return view('branch.chargeAccounts.edit', [
            'company' => $company,
            'branch' => $branch,
            'chargeAccount' => $chargeAccount,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $companySlug, string $branchSlug, string $id)
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
        $branch = $request->attributes->get('branch');

        $postData = $request->all();
        $postData['company_id'] = $company->id;
        $postData['branch_id'] = $branch->id;

        if ($this->chargeAccountRepository->update($id, $postData)) {
            return redirect()->route('branch.charge-accounts.index', ['companySlug' => $company->slug, 'branchSlug' => $branch->slug])->with('success', 'Data has been updated successfully!');
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
