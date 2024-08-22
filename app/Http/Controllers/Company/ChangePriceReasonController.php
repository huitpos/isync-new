<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\ChangePriceReason;

use App\DataTables\Company\PriceChangeReasonsDataTable;

class ChangePriceReasonController extends Controller
{
    
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, PriceChangeReasonsDataTable $dataTable)
    {
        $company = $request->attributes->get('company');

        return $dataTable->with('company_id', $company->id)
            ->render('company.changePriceReasons.index', compact('company'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $company = $request->attributes->get('company');

        return view('company.changePriceReasons.create', [
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

        if (ChangePriceReason::create($postData)) {
            return redirect()->route('company.change-price-reasons.index', ['companySlug' => $company->slug])->with('success', 'Change price reason created successfully.');
        }

        return redirect()->route('company.change-price-reasons.index', ['companySlug' => $company->slug])->with('error', 'Change price reason creation failed.');
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
        $reason = ChangePriceReason::findOrFail($id);

        return view('company.changePriceReasons.edit', [
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

        $reason = ChangePriceReason::findOrFail($id);

        if ($reason->update($request->all())) {
            return redirect()->route('company.change-price-reasons.index', ['companySlug' => $company->slug])->with('success', 'Change price reason updated successfully.');
        }

        return redirect()->route('company.change-price-reasons.index', ['companySlug' => $company->slug])->with('error', 'Change price reason update failed.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
