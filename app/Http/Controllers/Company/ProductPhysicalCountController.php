<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\DataTables\Company\ProductPhysicalCountsDataTable;

use App\Models\ProductPhysicalCount;

class ProductPhysicalCountController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, ProductPhysicalCountsDataTable $dataTable)
    {
        $company = $request->attributes->get('company');

        return $dataTable->with([
            'company_slug' => $company->slug,
            'company_id' => $company->id,
        ])->render('company.productPhysicalCounts.index', [
            'company' => $company,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, string $companySlug,  string $id)
    {
        $count = ProductPhysicalCount::with([
            'items',
            'createdBy'
        ])->findOrFail($id);

        $company = $request->attributes->get('company');
        $branch = $request->attributes->get('branch');

        return view('company.productPhysicalCounts.show', [
            'count' => $count,
            'company' => $company,
            'branch' => $branch
        ]);
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
