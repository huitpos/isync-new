<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

use App\DataTables\Company\StockTransferRequestsDataTable;

use App\Models\StockTransferRequest;
use App\Models\StockTransferOrder;

class StockTransferRequestController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, StockTransferRequestsDataTable $dataTable)
    {
        $company = $request->attributes->get('company');
        $permissions = $request->attributes->get('permissionNames');

        $branches = $company->branches;

        return $dataTable->with([
            'status' => $request->query('status', null),
            'branch_id' => $request->query('branch_id', null),
            'company_id' => $company->id,
            'company_slug' => $company->slug,
        ])->render('company.stockTransferRequests.index', [
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
    public function show(Request $request, string $slug, string $id)
    {
        $str = StockTransferRequest::with([
            'items',
            'createdBy'
        ])->findOrFail($id);

        $company = $request->attributes->get('company');

        return view('company.stockTransferRequests.show', [
            'str' => $str,
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
    public function update(Request $request, string $companySlug, string $id)
    {
        
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        
    }

    public function print(Request $request, string $slug, string $id)
    {
        $str = StockTransferRequest::with([
            'items',
            'createdBy'
        ])->findOrFail($id);

        $company = $request->attributes->get('company');

        $pdf = Pdf::loadView('company.stockTransferRequests.print', [
            'str' => $str,
            'company' => $company
        ]);

        return $pdf->download("STR-$str->str_number.pdf");
    }
}
