<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

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

        return $dataTable->with([
            'status' => $request->query('status', null),
            'company_id' => $company->id,
            'company_slug' => $company->slug,
        ])->render('company.stockTransferRequests.index', [
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
        $str = StockTransferRequest::findOrFail($id);

        $validatedData = $request->validate([
            'status' => 'required',
        ]);

        $data = $validatedData;
        $data['action_by'] = auth()->user()->id;

        if ($str->update($data)) {
            if ($request->status === 'approved') {
                $str = StockTransferRequest::findOrFail($id);
                $branch = $str->branch;

                $poCount = StockTransferOrder::where([
                    'source_branch_id' => $branch->id
                ])->count();

                $branchCode = strtoupper($branch->branch_code);
                $date = date('Ymd');
                $counter = str_pad($poCount+1, 4, '0', STR_PAD_LEFT);
                $stoNumber = "STO$branchCode$date$counter";

                $poData = [
                    'source_branch_id' => $str->source_branch_id,
                    'destination_branch_id' => $str->destination_branch_id,
                    'department_id' => $str->department_id,
                    'delivery_location_id' => $str->delivery_location_id,
                    'stock_transfer_request_id' => $str->id,
                    'sto_number' => $stoNumber,
                    'str_remarks' => $str->remarks,
                    'status' => 'pending',
                    'action_by' => auth()->user()->id,
                ];

                $transferRequestOrder = new StockTransferOrder();
                $transferRequestOrder->fill($poData);
                $transferRequestOrder->save();

                $stoItems = [];
                foreach ($str->items as $item) {
                    $stoItems[] = [
                        'product_id' => $item->product_id,
                        'uom_id' => $item->uom_id,
                        'quantity' => $item->quantity,
                        'str_remarks' => $item->remarks,
                    ];
                }

                $transferRequestOrder->items()->createMany($stoItems);
            }

            return redirect()->route('company.stock-transfer-requests.show', [
                'companySlug' => $companySlug,
                'stock_transfer_request' => $str->id
            ])->with('success', 'Stock transfer request updated.');
        }

        return redirect()->route('company.stock-transfer-requests.show', [
            'companySlug' => $companySlug,
            'stock_transfer_request' => $str->id
        ])->with('error', 'Failed to update stock transfer request.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        
    }
}
