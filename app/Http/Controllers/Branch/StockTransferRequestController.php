<?php

namespace App\Http\Controllers\Branch;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Department;
use App\Models\StockTransferRequest;
use App\Models\StockTransferOrder;

use App\DataTables\Branch\StockTransferRequestsDataTable;

class StockTransferRequestController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, StockTransferRequestsDataTable $dataTable)
    {
        $company = $request->attributes->get('company');
        $branch = $request->attributes->get('branch');

        return $dataTable->with([
            'branch_id' => $branch->id,
            'branch_slug' => $branch->slug,
            'company_slug' => $company->slug,
        ])->render('branch.stockTransferRequests.index', [
            'company' => $company,
            'branch' => $branch,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $company = $request->attributes->get('company');
        $branch = $request->attributes->get('branch');

        $departments = $company->departments()->where([
            'status' => 'active'
        ])->get();

        $branches = $company->branches()->where([
            'status' => 'active'
        ])
        ->where('id', '!=', $branch->id)
        ->get();

        $deliveryLocations = $branch->deliveryLocations()->with([
            'barangay',
            'city',
            'province',
            'region'
        ])->get();

        $suppliers = [];
        if (old('department_id')) {
            $department = Department::find(old('department_id'));
            $suppliers = $department->suppliers()->where([
                'status' => 'active'
            ])->get();
        }

        return view('branch.stockTransferRequests.create', [
            'company' => $company,
            'branch' => $branch,
            'suppliers' => $suppliers,
            'deliveryLocations' => $deliveryLocations,
            'departments' => $departments,
            'branches' => $branches
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'department_id' => 'required',
            'delivery_location_id' => 'required',
            'source_branch_id' => 'required',
            'pr_items' => 'required',
            'pr_items.*.product_id' => 'required',
            'pr_items.*.quantity' => 'required_with:pr_items.*.product_id',
            'pr_items.*.uom_id' => 'required_with:pr_items.*.product_id',
        ],
        [
            'pr_items' => 'Product is required',
            'pr_items.*.quantity' => 'Quantity field required',
            'pr_items.*.uom_id' => 'The product you selected has no UOM. Please assign a UOM first before continuing',
        ]);

        $branch = $request->attributes->get('branch');
        $company = $request->attributes->get('company');

        $strCount = StockTransferRequest::where([
            'destination_branch_id' => $branch->id
        ])->count();

        $branchCode = strtoupper($branch->code);
        $date = date('Ymd');
        $counter = str_pad($strCount+1, 4, '0', STR_PAD_LEFT);
        $strNumber = "STR$branchCode$date$counter";

        $postData = $request->all();

        $prData = $request->all();
        $prData['destination_branch_id'] = $branch->id;
        $prData['str_number'] = $strNumber;
        unset($prData['pr_items']);

        //save the purchase request and its items using model
        $stockTransferRequest = new StockTransferRequest();
        $stockTransferRequest->fill($prData);
        $stockTransferRequest->save();

        $stockTransferRequest->items()->createMany($postData['pr_items']);

        return redirect()->route('branch.stock-transfer-requests.index', ['companySlug' => $company->slug, 'branchSlug' => $branch->slug])->with('success', 'Stock Transfer Request has been created.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, string $companySlug, string $branchSlug, string $id)
    {
        $str = StockTransferRequest::with([
            'items',
            'createdBy'
        ])->findOrFail($id);

        $company = $request->attributes->get('company');
        $branch = $request->attributes->get('branch');

        return view('branch.stockTransferRequests.show', [
            'str' => $str,
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
    public function update(Request $request, string $companySlug, string $branchSlug, string $id)
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

                $branchCode = strtoupper($branch->code);
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

            return redirect()->route('branch.stock-transfer-requests.show', [
                'companySlug' => $companySlug,
                'branchSlug' => $branchSlug,
                'stock_transfer_request' => $str->id
            ])->with('success', 'Stock transfer request updated.');
        }

        return redirect()->route('branch.stock-transfer-requests.show', [
            'companySlug' => $companySlug,
            'branchSlug' => $branchSlug,
            'stock_transfer_request' => $str->id
        ])->with('error', 'Failed to update stock transfer request.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
