<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\ProductDisposalReason;

use App\DataTables\Company\ProductDisposalReasonsDataTable;

class ProductDisposalReasonController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, ProductDisposalReasonsDataTable $dataTable)
    {
        $company = $request->attributes->get('company');

        return $dataTable->with('company_id', $company->id)
            ->render('company.productDisposalReasons.index', compact('company'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $company = $request->attributes->get('company');

        return view('company.productDisposalReasons.create', [
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
        ]);

        $company = $request->attributes->get('company');

        $postData = $request->all();
        $postData['company_id'] = $company->id;

        if (ProductDisposalReason::create($postData)) {
            return redirect()->route('company.product-disposal-reasons.index', ['companySlug' => $company->slug])->with('success', 'Product disposal reason created successfully.');
        }

        return redirect()->route('company.product-disposal-reasons.index', ['companySlug' => $company->slug])->with('error', 'Product disposal reason creation failed.');
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
    public function edit(Request $request, string $companySlug, string $id)
    {
        $company = $request->attributes->get('company');
        $reason = ProductDisposalReason::findOrFail($id);

        return view('company.productDisposalReasons.edit', [
            'company' => $company,
            'reason' => $reason,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $companySlug, int $id)
    {
        $request->validate([
            'name' => 'required',
        ]);

        $company = $request->attributes->get('company');

        $reason = ProductDisposalReason::findOrFail($id);

        if ($reason->update($request->all())) {
            return redirect()->route('company.product-disposal-reasons.index', ['companySlug' => $company->slug])->with('success', 'Product disposal reason updated successfully.');
        }

        return redirect()->route('company.product-disposal-reasons.index', ['companySlug' => $company->slug])->with('error', 'Product disposal reason update failed.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
