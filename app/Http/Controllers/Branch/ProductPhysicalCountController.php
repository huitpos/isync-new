<?php

namespace App\Http\Controllers\Branch;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\ProductPhysicalCount;

use App\DataTables\Branch\ProductPhysicalCountsDataTable;

class ProductPhysicalCountController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, ProductPhysicalCountsDataTable $dataTable)
    {
        $company = $request->attributes->get('company');
        $branch = $request->attributes->get('branch');

        return $dataTable->with([
            'branch_id' => $branch->id,
            'branch_slug' => $branch->slug,
            'company_slug' => $company->slug,
        ])->render('branch.productPhysicalCounts.index', [
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

        return view('branch.productPhysicalCounts.create', [
            'company' => $company,
            'branch' => $branch,
            'departments' => $departments
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'department_id' => 'required',
            'pr_items' => 'required',
            'pr_items.*.product_id' => 'required',
            'pr_items.*.quantity' => 'required_with:pr_items.*.product_id',
        ],
        [
            'pr_items' => 'Product is required',
            'pr_items.*.quantity' => 'Quantity field required',
        ]);

        $branch = $request->attributes->get('branch');
        $company = $request->attributes->get('company');

        $postData = $request->all();

        $physicalCountData = $request->all();
        $physicalCountData['branch_id'] = $branch->id;
        $physicalCountData['action_by'] = auth()->user()->id;
        unset($physicalCountData['pr_items']);

        //save the purchase request and its items using model
        $physicalCount = new ProductPhysicalCount();
        $physicalCount->fill($physicalCountData);
        $physicalCount->save();

        $physicalCount->items()->createMany($postData['pr_items']);

        return redirect()->route('branch.stock-transfer-requests.index', ['companySlug' => $company->slug, 'branchSlug' => $branch->slug])->with('success', 'Stock Transfer Request has been created.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, string $companySlug, string $branchSlug, string $id)
    {
        $count = ProductPhysicalCount::with([
            'items',
            'createdBy'
        ])->findOrFail($id);

        $company = $request->attributes->get('company');
        $branch = $request->attributes->get('branch');

        return view('branch.productPhysicalCounts.show', [
            'count' => $count,
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
        $count = ProductPhysicalCount::with([
            'items',
            'createdBy'
        ])->findOrFail($id);

        $count->status = $request->status;
        $count->save();

        $company = $request->attributes->get('company');
        $branch = $request->attributes->get('branch');

        if ($count->status == 'approved') {
            foreach ($count->items as $item) {
                $product = $item->product;

                $newStock = $item->quantity;

                if ($branch->products()->where('product_id', $product->id)->exists()) {
                    // Product already exists in the branch, update the existing pivot record
                    $branch->products()->updateExistingPivot($product->id, [
                        'stock' => $newStock
                    ]);
                } else {
                    // Product doesn't exist in the branch, create a new pivot record
                    $branch->products()->attach($product->id, [
                        'stock' => $newStock
                    ]);
                }
            }
        }

        return redirect()->route('branch.product-physical-counts.index', ['companySlug' => $company->slug, 'branchSlug' => $branch->slug])->with('success', 'Data has been updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
