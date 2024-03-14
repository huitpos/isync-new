<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SupplierTerm;

use App\DataTables\Company\SupplierTermsDataTable;

class SupplierTermController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, SupplierTermsDataTable $dataTable)
    {
        $company = $request->attributes->get('company');

        return $dataTable->with('company_id', $company->id)
            ->render('company.supplierTerms.index', compact('company'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $company = $request->attributes->get('company');

        return view('company.supplierTerms.create', [
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
            'days' => 'required',
        ]);

        $company = $request->attributes->get('company');

        $postData = $request->all();
        $postData['company_id'] = $company->id;

        if (SupplierTerm::create($postData)) {
            return redirect()->route('company.supplier-terms.index', ['companySlug' => $company->slug])->with('success', 'Supplier term created successfully.');
        }

        return redirect()->route('company.supplier-terms.index', ['companySlug' => $company->slug])->with('error', 'Supplier term creation failed.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
