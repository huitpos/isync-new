<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\PurchaseRequest;
use App\Models\PurchaseOrder;

use App\DataTables\Company\PurchaseRequestsDataTable;

class PurchaseRequestController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, PurchaseRequestsDataTable $dataTable)
    {
        $company = $request->attributes->get('company');

        return $dataTable->with([
            'status' => $request->query('status', null),
            'company_id' => $company->id,
            'company_slug' => $company->slug,
        ])->render('company.purchaseRequests.index', [
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
    public function show(Request $request, string $slug, string $id)
    {
        $pr = PurchaseRequest::with([
            'items',
            'createdBy'
        ])->findOrFail($id);

        $company = $request->attributes->get('company');

        return view('company.purchaseRequests.show', [
            'pr' => $pr,
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
        $pr = PurchaseRequest::findOrFail($id);

        $validatedData = $request->validate([
            'payment_term_id' => 'required',
            'supplier_term_id' => 'required',
            'status' => 'required',
        ]);

        $data = $validatedData;
        $data['action_by'] = auth()->user()->id;

        if ($pr->update($data)) {
            if ($request->status === 'approved') {
                $pr = PurchaseRequest::findOrFail($id);
                $branch = $pr->branch;

                $poCount = PurchaseOrder::where([
                    'branch_id' => $branch->id
                ])->count();

                $branchCode = strtoupper($branch->branch_code);
                $date = date('Ymd');
                $counter = str_pad($poCount+1, 4, '0', STR_PAD_LEFT);
                $poNumber = "PO$branchCode$date$counter";

                $poData = [
                    'branch_id' => $pr->branch_id,
                    'department_id' => $pr->department_id,
                    'delivery_location_id' => $pr->delivery_location_id,
                    'supplier_id' => $pr->supplier_id,
                    'payment_term_id' => $pr->payment_term_id,
                    'supplier_term_id' => $pr->supplier_term_id,
                    'purchase_request_id' => $pr->id,
                    'po_number' => $poNumber,
                    'date_needed' => $pr->date_needed,
                    'pr_remarks' => $pr->remarks,
                    'total' => $pr->total,
                    'status' => 'approved',
                    'action_by' => auth()->user()->id,
                ];

                $purchaseOrder = new PurchaseOrder();
                $purchaseOrder->fill($poData);
                $purchaseOrder->save();

                $poItems = [];
                foreach ($pr->items as $item) {
                    $poItems[] = [
                        'product_id' => $item->product_id,
                        'uom_id' => $item->uom_id,
                        'unit_price' => $item->unit_price,
                        'quantity' => $item->quantity,
                        'balance' => $item->quantity,
                        'total' => $item->total,
                        'pr_remarks' => $item->remarks,
                    ];
                }

                $purchaseOrder->items()->createMany($poItems);
            }

            return redirect()->route('company.purchase-requests.show', [
                'companySlug' => $companySlug,
                'purchase_request' => $pr->id
            ])->with('success', 'Purchase request updated.');
        }

        return redirect()->route('company.purchase-requests.show', [
            'companySlug' => $companySlug,
            'purchase_request' => $pr->id
        ])->with('error', 'Failed to update purchase request.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
