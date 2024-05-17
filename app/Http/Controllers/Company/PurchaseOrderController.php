<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\DataTables\Company\PurchaseOrdersDataTable;

use App\Models\PurchaseOrder;

class PurchaseOrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, PurchaseOrdersDataTable $dataTable)
    {
        $company = $request->attributes->get('company');
        $branches = $company->branches;

        return $dataTable->with([
            'branch_id' => $request->query('branch_id', null),
            'company_id' => $company->id,
            'company_slug' => $company->slug,
        ])->render('company.purchaseOrders.index', [
            'company' => $company,
            'branches' => $branches,
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
    public function show(Request $request, string $companySlug, string $id)
    {
        $po = PurchaseOrder::with([
            'items',
            'createdBy'
        ])->findOrFail($id);

        $company = $request->attributes->get('company');

        return view('company.purchaseOrders.show', [
            'po' => $po,
            'company' => $company,
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
