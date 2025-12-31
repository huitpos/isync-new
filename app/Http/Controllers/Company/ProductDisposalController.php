<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\DataTables\Company\ProductDisposalsDataTable;

use App\Models\ProductDisposal;
use Barryvdh\DomPDF\Facade\Pdf;

class ProductDisposalController extends Controller
{

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, ProductDisposalsDataTable $dataTable)
    {
        $company = $request->attributes->get('company');
        $permissions = $request->attributes->get('permissionNames');
        $branches = $company->branches()->where([
            'status' => 'active'
        ])->get();

        return $dataTable->with([
            'status' => $request->query('status', null),
            'branch_id' => $request->query('branch_id', null),
            'company_slug' => $company->slug,
            'company_id' => $company->id,
            'permissions' => $permissions,
        ])->render('company.productDisposals.index', [
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
        $disposal = ProductDisposal::with([
            'items',
            'productDisposalReason',
            'createdBy'
        ])->findOrFail($id);

        $company = $request->attributes->get('company');

        return view('company.productDisposals.show', [
            'disposal' => $disposal,
            'company' => $company
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

    public function print(Request $request, string $slug, string $id)
    {
        $disposal = ProductDisposal::with([
            'items',
            'items.product',
            'items.uom',
            'productDisposalReason',
            'createdBy',
            'department'
        ])->findOrFail($id);

        $company = $request->attributes->get('company');

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('company.productDisposals.print', [
            'disposal' => $disposal,
            'company' => $company,
            'branch' => $disposal->branch
        ]);

        return $pdf->download("PD-$disposal->id.pdf");
    }
}
