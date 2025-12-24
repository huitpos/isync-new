<?php

namespace App\Http\Controllers\Branch;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Department;
use App\Models\PurchaseRequest;
use App\Models\PurchaseOrder;

use Barryvdh\DomPDF\Facade\Pdf;

use App\DataTables\Branch\PurchaseRequestsDataTable;

class PurchaseRequestController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, PurchaseRequestsDataTable $dataTable)
    {
        $company = $request->attributes->get('company');
        $branch = $request->attributes->get('branch');

        return $dataTable->with([
            'branch_id' => $branch->id,
            'branch_slug' => $branch->slug,
            'company_slug' => $company->slug,
            'status' => $request->query('status', null),
        ])->render('branch.purchaseRequests.index', [
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

        $deliveryLocations = $branch->deliveryLocations()
        ->where([
            'status' => 'active'
        ])
        ->with([
            'barangay',
            'city',
            'province',
            'region'
        ])->get();

        $suppliers = [];
        if (old('department_id') && old('department_id') != 'all') {
            $department = Department::find(old('department_id'));
            $suppliers = $department->suppliers()->where([
                'status' => 'active'
            ])->get();
        }

        return view('branch.purchaseRequests.create', [
            'company' => $company,
            'branch' => $branch,
            'suppliers' => $suppliers,
            'deliveryLocations' => $deliveryLocations,
            'departments' => $departments,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'department_id' => 'required',
            'date_needed' => [
                'required',
                'date',
                function ($attribute, $value, $fail) {
                    if (!strtotime($value) && !strtotime(str_replace('T', ' ', $value))) {
                        $fail($attribute . ' is not in a valid date-time format.');
                    }
                },
            ],
            'delivery_location_id' => 'required',
            'supplier_id' => 'required',
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

        $prCount = PurchaseRequest::where([
            'branch_id' => $branch->id
        ])->count();

        $branchCode = strtoupper($branch->code);
        $date = date('Ymd');
        $counter = str_pad($prCount+1, 4, '0', STR_PAD_LEFT);
        $prNumber = "PR$branchCode$date$counter";

        $postData = $request->all();

        $prData = $request->all();
        $prData['branch_id'] = $branch->id;
        $prData['pr_number'] = $prNumber;
        unset($prData['pr_items']);

        $prData['department_id'] = $postData['department_id'] == 'all' ? null : $postData['department_id'];

        //save the purchase request and its items using model
        $purchaseRequest = new PurchaseRequest();
        $purchaseRequest->fill($prData);
        $purchaseRequest->save();

        $purchaseRequest->items()->createMany($postData['pr_items']);

        return redirect()->route('branch.purchase-requests.index', ['companySlug' => $company->slug, 'branchSlug' => $branch->slug])->with('success', 'Purchase Request has been created.');

    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, string $companySlug, string $branchSlug, string $id)
    {
        $pr = PurchaseRequest::with([
            'items',
            'createdBy',
            'supplier',
            'actionBy'
        ])->findOrFail($id);

        $company = $request->attributes->get('company');
        $branch = $request->attributes->get('branch');

        if ($pr->status == 'pending') {
            $supplierTerms = $company->supplierTerms()->where([
                'status' => 'active'
            ])->get();

            $paymentTerms = $company->paymentTerms()->where([
                'status' => 'active'
            ])->get();
        } else {
            $paymentTerms = $company->paymentTerms;
            $supplierTerms = $company->supplierTerms;
        }

        return view('branch.purchaseRequests.show', [
            'pr' => $pr,
            'company' => $company,
            'branch' => $branch,
            'supplierTerms' => $supplierTerms,
            'paymentTerms'  => $paymentTerms
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
        $pr = PurchaseRequest::findOrFail($id);

        $validatedData = $request->validate([
            'payment_term_id' => 'required',
            'supplier_term_id' => 'required',
            'status' => 'required',
        ]);

        $data = $validatedData;
        $data['action_by'] = auth()->user()->id;
        $data['action_date'] = now();

        if ($pr->update($data)) {
            if ($request->status === 'approved') {
                $pr = PurchaseRequest::findOrFail($id);
                $branch = $pr->branch;

                $poCount = PurchaseOrder::where([
                    'branch_id' => $branch->id
                ])->count();

                $branchCode = strtoupper($branch->code);
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

            return redirect()->back()->with('success', 'Purchase request updated successfully');
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

    public function print(Request $request, string $slug, string $branchSlug, string $id)
    {
        $pr = PurchaseRequest::with([
            'items',
            'createdBy'
        ])->findOrFail($id);

        $company = $request->attributes->get('company');

        $pdf = Pdf::loadView('company.purchaseRequests.print', [
            'pr' => $pr,
            'company' => $company,
            'branch' => $pr->branch
        ]);

        return $pdf->download("PR-$pr->pr_number.pdf");
    }
}
