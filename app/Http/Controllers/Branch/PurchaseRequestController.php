<?php

namespace App\Http\Controllers\Branch;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Department;
use App\Models\PurchaseRequest;

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
                    // Check if the value matches either 'Y-m-d\TH:i' or 'Y-m-d' format
                    if (!strtotime($value) && !strtotime(str_replace('T', ' ', $value))) {
                        $fail($attribute . ' is not in a valid date-time format.');
                    }
                },
            ],
            'delivery_location_id' => 'required',
            'supplier_id' => 'required',
        ]);

        $branch = $request->attributes->get('branch');
        $company = $request->attributes->get('company');

        $prCount = PurchaseRequest::where([
            'branch_id' => $branch->id
        ])->count();

        $branchCode = strtoupper($branch->branch_code);
        $date = date('Ymd');
        $counter = str_pad($prCount+1, 4, '0', STR_PAD_LEFT);
        $prNumber = "PR$branchCode$date$counter";

        $postData = $request->all();

        $prData = $request->all();
        $prData['branch_id'] = $branch->id;
        $prData['pr_number'] = $prNumber;
        unset($prData['pr_items']);

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
            'createdBy'
        ])->findOrFail($id);

        $company = $request->attributes->get('company');
        $branch = $request->attributes->get('branch');

        return view('branch.purchaseRequests.show', [
            'pr' => $pr,
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