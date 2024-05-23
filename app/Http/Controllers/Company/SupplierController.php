<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Repositories\Interfaces\SupplierRepositoryInterface;

use App\DataTables\Company\SuppliersDataTable;

class SupplierController extends Controller
{
    protected $supplierRepository;

    public function __construct(
        SupplierRepositoryInterface $supplierRepository
    ) {
        $this->supplierRepository = $supplierRepository;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, SuppliersDataTable $dataTable)
    {
        $company = $request->attributes->get('company');
        $permissions = $request->attributes->get('permissionNames');

        return $dataTable->with([
            'company_id' => $company->id,
            'permissions' => $permissions
        ])->render('company.suppliers.index', [
            'company' => $company,
            'permissions' => $permissions
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $company = $request->attributes->get('company');

        $supplierTerms = $company->supplierTerms()->where([
            'status' => 'active'
        ])->get();

        return view('company.suppliers.create', compact('company', 'supplierTerms'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $company = $request->attributes->get('company');

        $request->validate([
            'status' => 'required',
            'name' => 'required',
            'contact_person' => 'required',
            'contact_number' => 'required',
            'email' => 'required|email',
            'address' => 'required',
        ]);

        $postData = $request->all();
        $postData['company_id'] = $company->id;

        if ($this->supplierRepository->create($postData)) {
            return redirect()->route('company.suppliers.index', ['companySlug' => $request->attributes->get('company')->slug])->with('success', 'Supplier created.');
        }

        return redirect()->back()->with('error', 'Supplier not created.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, string $companySlug, string $id)
    {
        $company = $request->attributes->get('company');

        $supplier = $this->supplierRepository->findOrFail($id);

        return view('company.suppliers.show', compact('company', 'supplier'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request, string $companySlug, string $supplierId)
    {
        $company = $request->attributes->get('company');

        $supplier = $this->supplierRepository->findOrFail($supplierId);

        return view('company.suppliers.edit', compact('company', 'supplier'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $companySlug, string $supplierId)
    {
        $request->validate([
            'status' => 'required',
            'name' => 'required',
            'contact_person' => 'required',
            'contact_number' => 'required',
            'email' => 'required|email',
            'address' => 'required',
        ]);

        $company = $request->attributes->get('company');

        $postData = $request->all();
        $postData['company_id'] = $company->id;

        if ($this->supplierRepository->update($supplierId, $postData)) {
            return redirect()->route('company.suppliers.index', ['companySlug' => $request->attributes->get('company')->slug])->with('success', 'Supplier successfully updated.');
        }

        return redirect()->back()->with('error', 'Supplier not created.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
