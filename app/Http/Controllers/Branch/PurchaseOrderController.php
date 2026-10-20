<?php

namespace App\Http\Controllers\Branch;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\DataTables\Branch\PurchaseOrdersDataTable;

use App\Models\PurchaseOrder;
use App\Services\ThreeWayMatchService;
use App\Support\PurchaseOrderStatus;

use Barryvdh\DomPDF\Facade\Pdf;

class PurchaseOrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, PurchaseOrdersDataTable $dataTable)
    {
        $company = $request->attributes->get('company');
        $branch = $request->attributes->get('branch');

        return $dataTable->with([
            'branch_id' => $branch->id,
            'branch_slug' => $branch->slug,
            'company_slug' => $company->slug,
            'search' => $request->query('search', null),
        ])->render('branch.purchaseOrders.index', [
            'company' => $company,
            'branch' => $branch,
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
    public function show(Request $request, string $companySlug, string $branchSlug, string $id)
    {
        $po = PurchaseOrder::with([
            'items',
            'createdBy'
        ])->findOrFail($id);

        $company = $request->attributes->get('company');
        $branch = $request->attributes->get('branch');
        $matchLines = app(ThreeWayMatchService::class)->purchaseOrderSummary($po);

        return view('branch.purchaseOrders.show', [
            'po' => $po,
            'company' => $company,
            'branch' => $branch,
            'matchLines' => $matchLines,
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
    public function update(Request $request, string $companySlug, string $branchSlug, string $id)
    {
        $branch = $request->attributes->get('branch');
        $po = PurchaseOrder::where('branch_id', $branch->id)->findOrFail($id);

        $validated = $request->validate([
            'status' => 'required|in:approved,rejected',
        ]);

        PurchaseOrderStatus::apply($po, $validated['status'], auth()->id());

        return redirect()->back()->with('success', 'Purchase order ' . $validated['status'] . '.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    public function print(Request $request, string $slug, string $branchSlug, string $id)
    {
        $pr = PurchaseOrder::with([
            'items',
            'createdBy'
        ])->findOrFail($id);

        $company = $request->attributes->get('company');

        $pdf = Pdf::loadView('company.purchaseOrders.print', [
            'pr' => $pr,
            'company' => $company,
            'branch' => $pr->branch
        ]);

        return $pdf->download("PO-$pr->po_number.pdf");
    }
}
