<?php

namespace App\Http\Controllers\Branch;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\ProductPhysicalCount;

use App\DataTables\Branch\ProductPhysicalCountsDataTable;
use App\Models\PurchaseOrder;
use App\Repositories\Interfaces\ProductRepositoryInterface;

class ProductPhysicalCountController extends Controller
{
    protected $productRepository;

    public function __construct(
        ProductRepositoryInterface $productRepository
    ) {
        $this->productRepository = $productRepository;
    }

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
            'status' => $request->query('status', null),
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
            'pr_items.*.uom_id' => 'required_with:pr_items.*.product_id',
        ],
        [
            'pr_items' => 'Product is required',
            'pr_items.*.quantity' => 'Quantity field required',
            'pr_items.*.uom_id' => 'The product you selected has no UOM. Please assign a UOM first before continuing'
        ]);

        $branch = $request->attributes->get('branch');
        $company = $request->attributes->get('company');

        $postData = $request->all();

        $physicalCountData = $request->all();
        $physicalCountData['branch_id'] = $branch->id;
        $physicalCountData['action_by'] = auth()->user()->id;
        $physicalCountData['action_date'] = now();
        unset($physicalCountData['pr_items']);
        unset($physicalCountData['pr_selected_product_text']);

        $pcountCount = PurchaseOrder::where([
            'branch_id' => $branch->id
        ])->count();

        $branchCode = strtoupper($branch->code);
        $date = date('Ymd');
        $counter = str_pad($pcountCount+1, 4, '0', STR_PAD_LEFT);
        $pcountNumber = "PCOUNT$branchCode$date$counter";

        $physicalCountData['pcount_number'] = $pcountNumber;

        //save the purchase request and its items using model
        $physicalCount = new ProductPhysicalCount();
        $physicalCount->fill($physicalCountData);
        $physicalCount->save();

        foreach ($postData['pr_items'] as &$item) {
            unset($item['pr_selected_product_text']);
            unset($item['pr_selected_uom_text']);
            unset($item['barcode']);
        }

        $physicalCount->items()->createMany($postData['pr_items']);

        return redirect()->route('branch.product-physical-counts.index', ['companySlug' => $company->slug, 'branchSlug' => $branch->slug])->with('success', 'Stock Transfer Request has been created.');
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

                $this->productRepository->updateBranchQuantity($product, $branch, $id, 'product_physical_counts', $item->quantity, null, 'replace', $item->uom_id);
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
