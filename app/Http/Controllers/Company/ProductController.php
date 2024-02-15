<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

use App\Repositories\Interfaces\ProductRepositoryInterface;
use App\Repositories\Interfaces\CategoryRepositoryInterface;
use App\Repositories\Interfaces\SubcategoryRepositoryInterface;
use App\DataTables\Company\ProductsDataTable;

class ProductController extends Controller
{
    protected $productRepository;
    protected $categoryRepository;
    protected $subcategoryRepository;

    public function __construct(
        ProductRepositoryInterface $productRepository,
        CategoryRepositoryInterface $categoryRepository,
        SubcategoryRepositoryInterface $subcategoryRepository
    ) {
        $this->productRepository = $productRepository;
        $this->categoryRepository = $categoryRepository;
        $this->subcategoryRepository = $subcategoryRepository;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, ProductsDataTable $dataTable)
    {
        $company = $request->attributes->get('company');

        return $dataTable->with('company_id', $company->id)->render('company.products.index', [
            'company' => $company
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $company = $request->attributes->get('company');
        $categories = [];
        $subcategories = [];

        if (!empty(old('department_id'))) {
            $categories = $this->categoryRepository->get([
                'department_id' => old('department_id'),
                'status' => 'active'
            ]);
        }

        if (!empty(old('category_id'))) {
            $subcategories = $this->subcategoryRepository->get([
                'category_id' => old('category_id'),
                'status' => 'active'
            ]);
        }

        $departments = $company->departments()->where('status', 'active')->get();

        return view('company.products.create', [
            'company' => $company,
            'categories' => $categories,
            'subcategories' => $subcategories,
            'departments' => $departments
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
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
            'srp' => ['required', 'numeric', 'regex:/^\d+(\.\d{1,4})?$/'],
            'cost' => ['required', 'numeric', 'regex:/^\d+(\.\d{1,4})?$/'],
            'markup' => ['required', 'numeric', 'regex:/^\d+(\.\d{1,4})?$/'],
            'status' => 'required',
            'minimum_stock_level' => ['required', 'numeric', 'regex:/^\d+(\.\d{1,4})?$/'],
            'maximum_stock_level' => ['required', 'numeric', 'regex:/^\d+(\.\d{1,4})?$/'],
            'stock_on_hand' => ['required', 'numeric', 'regex:/^\d+(\.\d{1,4})?$/'],
            'raw_items.*.product_id' => 'nullable',
            'raw_items.*.quantity' => 'required_with:raw_items.*.product_id',
            'raw_items.*.uom_id' => 'required_with:raw_items.*.product_id',
            'bundled_items.*.product_id' => 'nullable',
            'bundled_items.*.quantity' => 'required_with:bundled_items.*.product_id',
        ], [
            'raw_items.*.quantity.required_with' => 'The quantity field is required when a product is selected.',
            'raw_items.*.uom_id.required_with' => 'The unit of measurement field is required when a product is selected.',
            'bundled_items.*.quantity.required_with' => 'The quantity field is required when a product is selected.',
        ]);

        $company = $request->attributes->get('company');

        $productData = [
            'company_id' => $company->id,
            'name' => $request->input('name'),
            'description' => $request->input('description'),
            'abbreviation' => $request->input('abbreviation'),
            'department_id' => $request->input('department_id'),
            'category_id' => $request->input('category_id'),
            'subcategory_id' => $request->input('subcategory_id'),
            'uom_id' => $request->input('uom_id'),
            'item_type_id' => $request->input('item_type_id'),
            'image' => $request->input('image') ?? '',
            'code' => $request->input('code'),
            'barcode' => $request->input('barcode'),
            'srp' => $request->input('srp'),
            'cost' => $request->input('cost'),
            'markup' => $request->input('markup'),
            'serial_number' => $request->input('serial_number'),
            'vatable' => $request->input('vatable') ?? false,
            'discount_exempt' => $request->input('discount_exempt') ?? false,
            'open_price' => $request->input('open_price') ?? false,
            'status' => $request->input('status'),
            'minimum_stock_level' => $request->input('minimum_stock_level'),
            'maximum_stock_level' => $request->input('maximum_stock_level'),
            'stock_on_hand' => $request->input('stock_on_hand'),
        ];

        $bundledItems = [];
        if (!empty($request->input('bundled_items'))) {
            foreach ($request->input('bundled_items') as $bundle) {
                if (empty($bundle['product_id'])) {
                    continue;
                }

                $data = [
                    'product_id' => $bundle['product_id'],
                    'quantity' => $bundle['quantity'],
                ];

                $bundledItems[] = $data;
            }
        }

        $rawItems = [];
        if (!empty($request->input('raw_items'))) {
            foreach ($request->input('raw_items') as $raw) {
                if (empty($raw['product_id'])) {
                    continue;
                }

                $data = [
                    'product_id' => $raw['product_id'],
                    'quantity' => $raw['quantity'],
                    'uom_id' => $raw['uom_id'],
                ];

                $rawItems[] = $data;
            }
        }

        if ($product = $this->productRepository->create($productData, $bundledItems, $rawItems)) {
            $path = '';
            if ($file = $request->file('image')) {
                $customFileName = 'product_' . $product->id . '.' . $file->extension();
                $path = Storage::disk('s3')->putFileAs('product_images', $file, $customFileName, 'public');

                $product->image = $path;
                $product->save();
            }
            return redirect()->route('company.products.index', ['companySlug' => $company->slug])
                ->with('success', 'Product created successfully');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, string $companySlug, string $id)
    {
        $product = $this->productRepository->find($id);

        if (!$product) {
            return redirect()->back()->with('error', 'Product not found');
        }

        $company = $request->attributes->get('company');

        return view('company.products.show', [
            'product' => $product,
            'company' => $company,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request, string $companySlug, string $productId)
    {
        $company = $request->attributes->get('company');

        $product = $this->productRepository->find($productId);

        if (!$product) {
            return abort(404, 'Product not found');
        }

        $departments = $company->departments()->where('status', 'active')->get();
        $categories = $company->categories()->where([
            'status' => 'active',
            'department_id' => $product->department_id
        ])->get();

        $subcategories = $company->subcategories()->where([
            'status' => 'active',
            'category_id' => $product->category_id
        ])->get();

        return view('company.products.edit', [
            'company' => $company,
            'product' => $product,
            'departments' => $departments,
            'categories' => $categories,
            'subcategories' => $subcategories,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $companySlug, string $productId)
    {
        $request->validate([
            'name' => 'required',
            'description' => 'required',
            'abbreviation' => 'required',
            'department_id' => 'required',
            'category_id' => 'required',
            'subcategory_id' => 'required',
            'uom_id' => 'required',
            'item_type_id' => 'required',
            'code' => 'required',
            'barcode' => 'required',
            'srp' => ['required', 'numeric', 'regex:/^\d+(\.\d{1,4})?$/'],
            'cost' => ['required', 'numeric', 'regex:/^\d+(\.\d{1,4})?$/'],
            'markup' => ['required', 'numeric', 'regex:/^\d+(\.\d{1,4})?$/'],
            'status' => 'required',
            'minimum_stock_level' => ['required', 'numeric', 'regex:/^\d+(\.\d{1,4})?$/'],
            'maximum_stock_level' => ['required', 'numeric', 'regex:/^\d+(\.\d{1,4})?$/'],
            'stock_on_hand' => ['required', 'numeric', 'regex:/^\d+(\.\d{1,4})?$/'],
            'raw_items.*.product_id' => 'nullable',
            'raw_items.*.quantity' => 'required_with:raw_items.*.product_id',
            'raw_items.*.uom_id' => 'required_with:raw_items.*.product_id',
        ], [
            'raw_items.*.quantity.required_with' => 'The quantity field is required when a product is selected.',
            'raw_items.*.uom_id.required_with' => 'The unit of measurement field is required when a product is selected.',
        ]);

        $company = $request->attributes->get('company');

        $path = '';
        if ($file = $request->file('image')) {
            $customFileName = 'product_' . $productId . '.' . $file->extension();
            $path = Storage::disk('s3')->putFileAs('product_images', $file, $customFileName, 'public');
        }

        $productData = [
            'company_id' => $company->id,
            'name' => $request->input('name'),
            'description' => $request->input('description'),
            'abbreviation' => $request->input('abbreviation'),
            'department_id' => $request->input('department_id'),
            'category_id' => $request->input('category_id'),
            'subcategory_id' => $request->input('subcategory_id'),
            'uom_id' => $request->input('uom_id'),
            'item_type_id' => $request->input('item_type_id'),
            'image' => $path,
            'code' => $request->input('code'),
            'barcode' => $request->input('barcode'),
            'srp' => $request->input('srp'),
            'cost' => $request->input('cost'),
            'markup' => $request->input('markup'),
            'serial_number' => $request->input('serial_number'),
            'vatable' => $request->input('vatable') ?? false,
            'discount_exempt' => $request->input('discount_exempt') ?? false,
            'open_price' => $request->input('open_price') ?? false,
            'status' => $request->input('status'),
            'minimum_stock_level' => $request->input('minimum_stock_level'),
            'maximum_stock_level' => $request->input('maximum_stock_level'),
            'stock_on_hand' => $request->input('stock_on_hand'),
        ];

        $bundledItems = [];
        if (!empty($request->input('bundled_items'))) {
            foreach ($request->input('bundled_items') as $bundle) {
                if (empty($bundle['product_id'])) {
                    continue;
                }

                $data = [
                    'product_id' => $bundle['product_id'],
                    'quantity' => $bundle['quantity'],
                ];

                $bundledItems[] = $data;
            }
        }

        $rawItems = [];
        if (!empty($request->input('raw_items'))) {
            foreach ($request->input('raw_items') as $raw) {
                if (empty($raw['product_id'])) {
                    continue;
                }

                $data = [
                    'product_id' => $raw['product_id'],
                    'quantity' => $raw['quantity'],
                    'uom_id' => $raw['uom_id'],
                ];

                $rawItems[] = $data;
            }
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
