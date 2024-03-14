<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PaymentTerm;

use App\DataTables\Company\PaymentTermsDataTable;

class PaymentTermController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, PaymentTermsDataTable $dataTable)
    {
        $company = $request->attributes->get('company');

        return $dataTable->with('company_id', $company->id)
            ->render('company.paymentTerms.index', compact('company'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $company = $request->attributes->get('company');

        return view('company.paymentTerms.create', [
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

        if (!empty($postData['is_default'])) {
            PaymentTerm::where('company_id', $company->id)->update(['is_default' => 0]);
        }

        if (PaymentTerm::create($postData)) {
            return redirect()->route('company.payment-terms.index', ['companySlug' => $company->slug])->with('success', 'Payment term created successfully.');
        }

        return redirect()->route('company.payment-terms.index', ['companySlug' => $company->slug])->with('error', 'Payment term creation failed.');
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
