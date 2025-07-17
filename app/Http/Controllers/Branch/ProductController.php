<?php

namespace App\Http\Controllers\Branch;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\DataTables\Branch\ProductsDataTable;

use App\Models\Product;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, ProductsDataTable $dataTable)
    {
        $company = $request->attributes->get('company');
        $branch = $request->attributes->get('branch');

        return $dataTable
            ->with('company_id', $company->id)
            ->with('branch_id', $branch->id)
            ->with('company_slug', $company->slug)
            ->with('branch_slug', $branch->slug)
            ->render('branch.products.index');
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
        $company = $request->attributes->get('company');
        $branch = $request->attributes->get('branch');

        $product = Product::findOrFail($id);

        $pivotData = $product->branches->where('id', $branch->id)->first()?->pivot;

        return view('branch.products.show', compact('company', 'branch','product', 'pivotData'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request, string $companySlug, string $branchSlug, string $id)
    {
        $company = $request->attributes->get('company');
        $branch = $request->attributes->get('branch');

        $product = Product::findOrFail($id);

        $pivotData = $product->branches->where('id', $branch->id)->first()?->pivot;

        return view('branch.products.edit', compact('company', 'branch','product', 'pivotData'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $companySlug, string $branchSlug, $id)
    {
        $company = $request->attributes->get('company');
        $branch = $request->attributes->get('branch');

        $request->validate([
            'price' => 'required',
            'cost' => 'required',
            'markup' => 'required',
        ]);

        $updateData = [
            'price' => $request->price,
            'cost' => $request->cost,
            'markup' => $request->markup,
        ];

        if ($branch->products()->where('product_id', $id)->exists()) {
            // Product already exists in the branch, update the existing pivot record
            $branch->products()->updateExistingPivot($id, $updateData);
        } else {
            // Product doesn't exist in the branch, create a new pivot record
            $branch->products()->attach($id, $updateData);
        }

        return redirect()->route('branch.products.index', ['companySlug' => $company->slug, 'branchSlug' => $branch->slug])
                ->with('success', 'Product updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
