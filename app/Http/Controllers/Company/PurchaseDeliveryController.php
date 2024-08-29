<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\DataTables\Company\PurchaseDeliveriesDataTable;

use App\Models\PurchaseDelivery;
use App\Models\Branch;

use Barryvdh\DomPDF\Facade\Pdf;

class PurchaseDeliveryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, PurchaseDeliveriesDataTable $dataTable)
    {
        $company = $request->attributes->get('company');
        $permissions = $request->attributes->get('permissionNames');
        $branches = $company->branches;

        return $dataTable->with([
            'status' => $request->query('status', null),
            'branch_id' => $request->query('branch_id', null),
            'company_id' => $company->id,
            'company_slug' => $company->slug,
        ])->render('company.purchaseDeliveries.index', [
            'company' => $company,
            'branches' => $branches,
            'permissions' => $permissions,
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
        $company = $request->attributes->get('company');

        $pd = PurchaseDelivery::with(['purchaseOrder'])->findOrFail($id);

        return view('company.purchaseDeliveries.show', [
            'company' => $company,
            'pd' => $pd,
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
    public function update(Request $request, string $companySlug, string $id)
    {
        
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    public function print(Request $request, string $slug, string $id)
    {
        $pr = PurchaseDelivery::with([
            'items',
            'createdBy'
        ])->findOrFail($id);

        $company = $request->attributes->get('company');

        $pdf = Pdf::loadView('company.purchaseDeliveries.print', [
            'pr' => $pr,
            'company' => $company,
            'branch' => $pr->branch
        ]);

        return $pdf->download("PD-$pr->pd_number.pdf");
    }
}
