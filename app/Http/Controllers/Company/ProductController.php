<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

use App\Repositories\Interfaces\ProductRepositoryInterface;
use App\Repositories\Interfaces\CategoryRepositoryInterface;
use App\Repositories\Interfaces\SubcategoryRepositoryInterface;
use App\DataTables\Company\ProductsDataTable;
use App\DataTables\Company\InventoryProductsDataTable;
use App\DataTables\Company\ProductCountHistoryDataTable;

use App\Imports\ProductsImport;
use App\Jobs\ProcessExcelJob;
use Maatwebsite\Excel\Facades\Excel;

use App\Models\Product;
use App\Models\Branch;
use App\Models\DiscountType;

use Carbon\Carbon;

use App\Exports\InventoryExport;
use App\Exports\ProductsExport;

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
        $permissions = $request->attributes->get('permissionNames');

        return $dataTable->with([
            'company_id' => $company->id,
            'permissions' => $permissions
        ])->render('company.products.index', [
            'company' => $company,
            'permissions' => $permissions
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
        $itemTypes = $company->itemTypes()->where('status', 'active')->get();

        $discountTypes = DiscountType::where('company_id', $company->id)
            ->orWhere('company_id', null)
            ->orderBy('id')
            ->get();

        return view('company.products.create', [
            'company' => $company,
            'categories' => $categories,
            'subcategories' => $subcategories,
            'departments' => $departments,
            'itemTypes' => $itemTypes,
            'discountTypes' => $discountTypes,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $company = $request->attributes->get('company');
        $request->validate([
            'name' => 'required|unique:products,name,NULL,id,company_id,' . $company->id,
            'description' => 'required',
            'abbreviation' => 'required',
            'department_id' => 'required',
            'category_id' => 'required',
            'subcategory_id' => 'required',
            'uom_id' => 'required',
            'delivery_uom_id' => 'required',
            'item_type_id' => 'required',
            'sku' => 'nullable|unique:products,sku,NULL,id,company_id,' . $company->id,
            'barcode' => 'nullable|unique:products,barcode,NULL,id,company_id,' . $company->id,
            'srp' => ['required', 'numeric', 'regex:/^-?\d+(\.\d{1,4})?$/'],
            'cost' => ['required', 'numeric', 'regex:/^-?\d+(\.\d{1,4})?$/'],
            'markup' => ['required', 'numeric', 'regex:/^-?\d+(\.\d{1,4})?$/'],
            'status' => 'required',
            'minimum_stock_level' => ['required', 'numeric', 'regex:/^-?\d+(\.\d{1,4})?$/'],
            'maximum_stock_level' => ['required', 'numeric', 'regex:/^-?\d+(\.\d{1,4})?$/'],
            'raw_items.*.product_id' => 'nullable',
            'raw_items.*.quantity' => 'required_with:raw_items.*.product_id',
            'raw_items.*.uom_id' => 'required_with:raw_items.*.product_id',
            'bundled_items.*.product_id' => 'nullable',
            'bundled_items.*.quantity' => 'required_with:bundled_items.*.product_id',
            'max_discount' => [
                'numeric', 'regex:/^-?\d+(\.\d{1,4})?$/',
                function ($attribute, $value, $fail) use ($request) {
                    if ($value < $request->input('cost')) {
                        $fail('The ' . $attribute . ' must be greater than cost.');
                    }
                },
            ]
        ], [
            'raw_items.*.quantity.required_with' => 'The quantity field is required when a product is selected.',
            'raw_items.*.uom_id.required_with' => 'The unit of measurement field is required when a product is selected.',
            'bundled_items.*.quantity.required_with' => 'The quantity field is required when a product is selected.',
        ]);

        $productData = [
            'company_id' => $company->id,
            'name' => $request->input('name'),
            'description' => $request->input('description'),
            'abbreviation' => $request->input('abbreviation'),
            'department_id' => $request->input('department_id'),
            'category_id' => $request->input('category_id'),
            'subcategory_id' => $request->input('subcategory_id'),
            'uom_id' => $request->input('uom_id'),
            'delivery_uom_id' => $request->input('delivery_uom_id'),
            'item_type_id' => $request->input('item_type_id'),
            'image' => $request->input('image') ?? '',
            'sku' => $request->input('sku'),
            'barcode' => $request->input('barcode'),
            'srp' => $request->input('srp'),
            'cost' => $request->input('cost'),
            'markup' => $request->input('markup'),
            'with_serial' => $request->input('with_serial') ?? false,
            'vat_exempt' => $request->input('vat_exempt') ?? false,
            'discount_exempt' => $request->input('discount_exempt') ?? false,
            'open_price' => $request->input('open_price') ?? false,
            'status' => $request->input('status'),
            'minimum_stock_level' => $request->input('minimum_stock_level'),
            'maximum_stock_level' => $request->input('maximum_stock_level'),
            'item_locations' => $request->input('item_locations'),
            'max_discount' => $request->input('max_discount'),
            'part_number' => $request->input('part_number'),
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

        $discounts = [];
        if (!empty($request->input('discounts'))) {
            foreach ($request->input('discounts') as $discount) {
                if (empty($discount['discount_type_id'])) {
                    continue;
                }

                $data = [
                    'discount_type_id' => $discount['discount_type_id'],
                    'type' => $discount['type'],
                    'discount' => $discount['discount'],
                ];

                $discounts[] = $data;
            }
        }

        if ($product = $this->productRepository->create($productData, $bundledItems, $rawItems)) {
            if (!empty($discounts)) {
                $product->discounts()->detach();
    
                foreach ($discounts as $discount) {
                    $product->discounts()->attach($discount['discount_type_id'], [
                        'type' => $discount['type'],
                        'discount' => $discount['discount']
                    ]);
                }
            }

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

        $itemTypes = $company->itemTypes()->where('status', 'active')->get();

        $discountTypes = DiscountType::where('company_id', $company->id)
            ->orWhere('company_id', null)
            ->orderBy('id')
            ->get();

        return view('company.products.edit', [
            'company' => $company,
            'product' => $product,
            'departments' => $departments,
            'categories' => $categories,
            'subcategories' => $subcategories,
            'itemTypes' => $itemTypes,
            'discountTypes' => $discountTypes,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $companySlug, string $productId)
    {
        $company = $request->attributes->get('company');
        
        $request->validate([
            'name' => 'required|unique:products,name,' . $productId . ',id,company_id,' . $company->id,
            'description' => 'required',
            'abbreviation' => 'required',
            'department_id' => 'required',
            'category_id' => 'required',
            'subcategory_id' => 'required',
            'uom_id' => 'required',
            'delivery_uom_id' => 'required',
            'item_type_id' => 'required',
            'sku' => 'nullable|unique:products,sku,' . $productId . ',id,company_id,' . $company->id,
            'barcode' => 'nullable|unique:products,barcode,' . $productId . ',id,company_id,' . $company->id,
            'srp' => ['required', 'numeric', 'regex:/^-?\d+(\.\d{1,4})?$/'],
            'cost' => ['required', 'numeric', 'regex:/^-?\d+(\.\d{1,4})?$/'],
            'markup' => ['required', 'numeric', 'regex:/^-?\d+(\.\d{1,4})?$/'],
            'status' => 'required',
            'minimum_stock_level' => ['required', 'numeric', 'regex:/^-?\d+(\.\d{1,4})?$/'],
            'maximum_stock_level' => ['required', 'numeric', 'regex:/^-?\d+(\.\d{1,4})?$/'],
            'raw_items.*.product_id' => 'nullable',
            'raw_items.*.quantity' => 'required_with:raw_items.*.product_id',
            'raw_items.*.uom_id' => 'required_with:raw_items.*.product_id',
            'max_discount' => [
                'numeric', 'regex:/^-?\d+(\.\d{1,4})?$/',
                function ($attribute, $value, $fail) use ($request) {
                    if ($value < $request->input('cost')) {
                        $fail('The ' . $attribute . ' must be greater than cost.');
                    }
                },
            ]
        ], [
            'raw_items.*.quantity.required_with' => 'The quantity field is required when a product is selected.',
            'raw_items.*.uom_id.required_with' => 'The unit of measurement field is required when a product is selected.',
        ]);

        $productData = [
            'company_id' => $company->id,
            'name' => $request->input('name'),
            'description' => $request->input('description'),
            'abbreviation' => $request->input('abbreviation'),
            'department_id' => $request->input('department_id'),
            'category_id' => $request->input('category_id'),
            'subcategory_id' => $request->input('subcategory_id'),
            'uom_id' => $request->input('uom_id'),
            'delivery_uom_id' => $request->input('delivery_uom_id'),
            'item_type_id' => $request->input('item_type_id'),
            'sku' => $request->input('sku'),
            'barcode' => $request->input('barcode'),
            'srp' => $request->input('srp'),
            'cost' => $request->input('cost'),
            'markup' => $request->input('markup'),
            'with_serial' => $request->input('with_serial') ?? false,
            'vat_exempt' => $request->input('vat_exempt') ?? false,
            'discount_exempt' => $request->input('discount_exempt') ?? false,
            'open_price' => $request->input('open_price') ?? false,
            'status' => $request->input('status'),
            'minimum_stock_level' => $request->input('minimum_stock_level'),
            'maximum_stock_level' => $request->input('maximum_stock_level'),
            'item_locations' => $request->input('item_locations'),
            'max_discount' => $request->input('max_discount'),
            'part_number' => $request->input('part_number'),
        ];

        if ($file = $request->file('image')) {
            $customFileName = 'product_' . $productId . '.' . $file->extension();
            $path = Storage::disk('s3')->putFileAs('product_images', $file, $customFileName, 'public');

            $productData['image'] = $path;
        }

        if ($request->image_remove) {
            $productData['image'] = null;
        }

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
        
        $discounts = [];
        if (!empty($request->input('discounts'))) {
            foreach ($request->input('discounts') as $discount) {
                if (empty($discount['discount_type_id'])) {
                    continue;
                }

                $data = [
                    'discount_type_id' => $discount['discount_type_id'],
                    'type' => $discount['type'],
                    'discount' => $discount['discount'],
                ];

                $discounts[] = $data;
            }
        }

        if ($product = $this->productRepository->update($productId, $productData, $bundledItems, $rawItems)) {
            if (!empty($discounts)) {
                $product->discounts()->detach();
    
                foreach ($discounts as $discount) {
                    $product->discounts()->attach($discount['discount_type_id'], [
                        'type' => $discount['type'],
                        'discount' => $discount['discount']
                    ]);
                }
            }

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

    public function import(Request $request)
    {
        $company = $request->attributes->get('company');

        $request->validate([
            'file' => 'required|mimes:xlsx',
        ]);

        $file = $request->file('file');
        $path = $file->store('uploads');

        ProcessExcelJob::dispatch($path, $company->id);

        return redirect()->route('company.products.index', ['companySlug' => $company->slug])
                ->with('success', 'Products import started');
    }

    public function showForm(Request $request)
    {
        $company = $request->attributes->get('company');

        return view('company.products.import', [
            'company' => $company
        ]);
    }

    public function inventory(Request $request, $companySlug, $branchId, InventoryProductsDataTable $dataTable)
    {
        $company = $request->attributes->get('company');
        $branches = auth()->user()->activeBranches;
        $permissions = $request->attributes->get('permissionNames');

        return $dataTable->with('company_id', $company->id)
            ->with('branch_id', $branchId)
            ->with('permissions', $permissions)
            ->render('company.products.inventory_products', [
                'company' => $company,
                'branches' => $branches,
                'branchId' => $branchId,
            ]);
    }

    public function inventoryProduct(Request $request, $companySlug, $branchId, $productId, ProductCountHistoryDataTable $dataTable)
    {
        $company = $request->attributes->get('company');
        $branches = auth()->user()->activeBranches;

        $product = $this->productRepository->find($productId);

        $branch = Branch::find($branchId);
        return $dataTable->with('company_id', $company->id)
            ->with('branch_id', $branchId)
            ->with('product_id', $productId)
            ->render('company.products.count_history', [
                'company' => $company,
                'branches' => $branches,
                'branchId' => $branchId,
                'branch' => $branch,
                'product' => $product
            ]);
    }

    public function inventoryDownload (Request $request, $companySlug, $branchId)
    {
        $company = $request->attributes->get('company');
        $branch = Branch::find($branchId);

        return Excel::download(new InventoryExport($branch->id), "$company->name - $branch->name - ".Carbon::now()->format('Y-m-d 23:59:59')." - Inventory.xlsx");
    }

    /**
     * Export products listing to Excel
     */
    public function export(Request $request, $companySlug)
    {
        $company = $request->attributes->get('company');
        return Excel::download(
            new ProductsExport($company->id),
            "$company->name - Products - ".Carbon::now()->format('Y-m-d')." - List.xlsx"
        );
    }
}
