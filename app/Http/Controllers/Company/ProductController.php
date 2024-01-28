<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Repositories\Interfaces\ProductRepositoryInterface;

class ProductController extends Controller
{
    protected $productRepository;

    public function __construct(ProductRepositoryInterface $productRepository)
    {
        $this->productRepository = $productRepository;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $company = $request->attributes->get('company');

        return view('company.products.index', [
            'company' => $company
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $company = $request->attributes->get('company');

        return view('company.products.create', [
            'company' => $company
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $bundledItems = $request->input('bundled_items');
        if (isset($bundledItems[0])) {
            unset($bundledItems[0]);
        }

        $request->merge(['bundled_items' => $bundledItems]);

        $rawItems = $request->input('raw_items');
        if (isset($rawItems[0])) {
            unset($rawItems[0]);
        }

        $request->merge(['raw_items' => $rawItems]);

        $request->validate([
            'company_id' => 'required',
            'name' => 'required',
            'description' => 'required',
            'abbreviation' => 'required',
            'department_id' => 'required',
            'category_id' => 'required',
            'subcategory_id' => 'required',
            'uom_id' => 'required',
            'item_type_id' => 'required',
            // 'image' => 'required',
            'code' => 'required',
            'barcode' => 'required',
            'srp' => ['required', 'numeric', 'regex:/^\d+(\.\d{1,2})?$/'],
            'cost' => ['required', 'numeric', 'regex:/^\d+(\.\d{1,2})?$/'],
            'markup' => ['required', 'numeric', 'regex:/^\d+(\.\d{1,2})?$/'],
            'status' => 'required',
            'minimum_stock_level' => ['required', 'numeric', 'regex:/^\d+(\.\d{1,2})?$/'],
            'maximum_stock_level' => ['required', 'numeric', 'regex:/^\d+(\.\d{1,2})?$/'],
            'stock_on_hand' => ['required', 'numeric', 'regex:/^\d+(\.\d{1,2})?$/'],
        ]);

        $company = $request->attributes->get('company');

        $productData = [
            'company_id' => $request->input('company_id'),
            'name' => $request->input('name'),
            'description' => $request->input('description'),
            'abbreviation' => $request->input('abbreviation'),
            'department_id' => $request->input('department_id'),
            'category_id' => $request->input('category_id'),
            'subcategory_id' => $request->input('subcategory_id'),
            'uom_id' => $request->input('uom_id'),
            'item_type_id' => $request->input('item_type_id'),
            'image' => $request->input('image') ?? 'asdf',
            'code' => $request->input('code'),
            'barcode' => $request->input('barcode'),
            'srp' => $request->input('srp'),
            'cost' => $request->input('cost'),
            'markup' => $request->input('markup'),
            'with_serial' => $request->input('with_serial') ?? false,
            'vatable' => $request->input('vatable') ?? false,
            'discount_exempt' => $request->input('discount_exempt') ?? false,
            'open_price' => $request->input('open_price') ?? false,
            'status' => $request->input('status'),
            'minimum_stock_level' => $request->input('minimum_stock_level'),
            'maximum_stock_level' => $request->input('maximum_stock_level'),
            'stock_on_hand' => $request->input('stock_on_hand'),
        ];

        $bundledItems = [];
        foreach ($request->input('bundled_items') as $bundle) {
            $data = [
                'product_id' => $bundle['product_id'],
                'quantity' => $bundle['quantity'],
            ];

            $bundledItems[] = $data;
        }

        $rawItems = [];
        foreach ($request->input('raw_items') as $raw) {
            $data = [
                'product_id' => $raw['product_id'],
                'quantity' => $raw['quantity'],
            ];

            $rawItems[] = $data;
        }

        if ($this->productRepository->create($productData, $bundledItems, $rawItems)) {
            return redirect()->route('company.products.index', ['companySlug' => $company->slug])
                ->with('success', 'Product created successfully');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request, string $companySlug, string $productId)
    {
        $company = $request->attributes->get('company');

        $product = $this->productRepository->find($productId);

        return view('company.products.edit', [
            'company' => $company,
            'product' => $product,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $companySlug, string $productId)
    {
        $bundledItems = $request->input('bundled_items');
        if (isset($bundledItems[0])) {
            unset($bundledItems[0]);
        }

        $request->merge(['bundled_items' => $bundledItems]);

        $rawItems = $request->input('raw_items');
        if (isset($rawItems[0])) {
            unset($rawItems[0]);
        }

        $request->merge(['raw_items' => $rawItems]);

        $request->validate([
            'company_id' => 'required',
            'name' => 'required',
            'description' => 'required',
            'abbreviation' => 'required',
            'department_id' => 'required',
            'category_id' => 'required',
            'subcategory_id' => 'required',
            'uom_id' => 'required',
            'item_type_id' => 'required',
            // 'image' => 'required',
            'code' => 'required',
            'barcode' => 'required',
            'srp' => ['required', 'numeric', 'regex:/^\d+(\.\d{1,2})?$/'],
            'cost' => ['required', 'numeric', 'regex:/^\d+(\.\d{1,2})?$/'],
            'markup' => ['required', 'numeric', 'regex:/^\d+(\.\d{1,2})?$/'],
            'status' => 'required',
            'minimum_stock_level' => ['required', 'numeric', 'regex:/^\d+(\.\d{1,2})?$/'],
            'maximum_stock_level' => ['required', 'numeric', 'regex:/^\d+(\.\d{1,2})?$/'],
            'stock_on_hand' => ['required', 'numeric', 'regex:/^\d+(\.\d{1,2})?$/'],
        ]);

        $company = $request->attributes->get('company');

        $productData = [
            'company_id' => $request->input('company_id'),
            'name' => $request->input('name'),
            'description' => $request->input('description'),
            'abbreviation' => $request->input('abbreviation'),
            'department_id' => $request->input('department_id'),
            'category_id' => $request->input('category_id'),
            'subcategory_id' => $request->input('subcategory_id'),
            'uom_id' => $request->input('uom_id'),
            'item_type_id' => $request->input('item_type_id'),
            'image' => $request->input('image') ?? 'asdf',
            'code' => $request->input('code'),
            'barcode' => $request->input('barcode'),
            'srp' => $request->input('srp'),
            'cost' => $request->input('cost'),
            'markup' => $request->input('markup'),
            'with_serial' => $request->input('with_serial') ?? false,
            'vatable' => $request->input('vatable') ?? false,
            'discount_exempt' => $request->input('discount_exempt') ?? false,
            'open_price' => $request->input('open_price') ?? false,
            'status' => $request->input('status'),
            'minimum_stock_level' => $request->input('minimum_stock_level'),
            'maximum_stock_level' => $request->input('maximum_stock_level'),
            'stock_on_hand' => $request->input('stock_on_hand'),
        ];

        $bundledItems = [];
        foreach ($request->input('bundled_items') as $bundle) {
            $data = [
                'product_id' => $bundle['product_id'],
                'quantity' => $bundle['quantity'],
            ];

            $bundledItems[] = $data;
        }

        $rawItems = [];
        foreach ($request->input('raw_items') as $raw) {
            $data = [
                'product_id' => $raw['product_id'],
                'quantity' => $raw['quantity'],
            ];

            $rawItems[] = $data;
        }

        if ($this->productRepository->update($productId, $productData, $bundledItems, $rawItems)) {
            return redirect()->route('company.products.index', ['companySlug' => $company->slug])
                ->with('success', 'Product updated successfully');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
