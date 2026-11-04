<?php

namespace App\Http\Controllers\Branch;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

use App\Models\ProductPhysicalCount;

use App\DataTables\Branch\ProductPhysicalCountsDataTable;
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
            'departments' => $departments,
            'count' => null,
            'items' => old('pr_items', []),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $branch = $request->attributes->get('branch');
        $company = $request->attributes->get('company');

        $physicalCount = $this->savePhysicalCount($request, $branch);

        $message = $physicalCount->status === 'draft'
            ? 'Product physical count has been saved as draft.'
            : 'Product physical count has been submitted.';

        return redirect()->route('branch.product-physical-counts.index', [
            'companySlug' => $company->slug,
            'branchSlug' => $branch->slug,
        ])->with('success', $message);
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
    public function edit(Request $request, string $companySlug, string $branchSlug, string $id)
    {
        $company = $request->attributes->get('company');
        $branch = $request->attributes->get('branch');

        $count = ProductPhysicalCount::with([
            'items.product',
            'items.uom',
            'createdBy',
        ])->where('branch_id', $branch->id)->findOrFail($id);

        if ($count->status !== 'draft' || !$this->userOwnsDraft($count)) {
            return redirect()->route('branch.product-physical-counts.show', [
                'companySlug' => $company->slug,
                'branchSlug' => $branch->slug,
                'product_physical_count' => $count->id,
            ])->with('error', $count->status !== 'draft'
                ? 'Only draft physical counts can be edited.'
                : 'Only the user who saved this draft can edit it.');
        }

        $departments = $company->departments()->where([
            'status' => 'active'
        ])->get();

        $items = old('pr_items');
        if ($items === null) {
            $items = $count->items->map(function ($item) {
                return [
                    'product_id' => $item->product_id,
                    'pr_selected_product_text' => $item->product?->name ?? '',
                    'uom_id' => $item->uom_id,
                    'pr_selected_uom_text' => $item->uom?->name ?? '',
                    'barcode' => $item->product?->barcode ?? '',
                    'quantity' => $item->quantity,
                    'remarks' => $item->remarks,
                ];
            })->all();
        }

        return view('branch.productPhysicalCounts.create', [
            'company' => $company,
            'branch' => $branch,
            'departments' => $departments,
            'count' => $count,
            'items' => $items,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $companySlug, string $branchSlug, string $id)
    {
        $company = $request->attributes->get('company');
        $branch = $request->attributes->get('branch');

        $count = ProductPhysicalCount::with([
            'items',
            'createdBy'
        ])->where('branch_id', $branch->id)->findOrFail($id);

        if ($count->status === 'draft') {
            if (!$this->userOwnsDraft($count)) {
                return redirect()->route('branch.product-physical-counts.show', [
                    'companySlug' => $company->slug,
                    'branchSlug' => $branch->slug,
                    'product_physical_count' => $count->id,
                ])->with('error', 'Only the user who saved this draft can edit it.');
            }

            $count = $this->savePhysicalCount($request, $branch, $count);

            $message = $count->status === 'draft'
                ? 'Draft has been updated.'
                : 'Product physical count has been submitted.';

            return redirect()->route('branch.product-physical-counts.index', [
                'companySlug' => $company->slug,
                'branchSlug' => $branch->slug,
            ])->with('success', $message);
        }

        if ($count->status !== 'pending' || !in_array($request->status, ['approved', 'rejected'], true)) {
            return redirect()->route('branch.product-physical-counts.show', [
                'companySlug' => $company->slug,
                'branchSlug' => $branch->slug,
                'product_physical_count' => $count->id,
            ])->with('error', 'This physical count can no longer be updated.');
        }

        $count->status = $request->status;
        $count->save();

        if ($count->status == 'approved') {
            foreach ($count->items as $item) {
                $product = $item->product;

                $this->productRepository->updateBranchQuantity($product, $branch, $id, 'product_physical_counts', $item->quantity, null, 'replace', $item->uom_id);
            }
        }

        return redirect()->route('branch.product-physical-counts.index', ['companySlug' => $company->slug, 'branchSlug' => $branch->slug])->with('success', 'Data has been updated successfully!');
    }

    private function savePhysicalCount(Request $request, $branch, ?ProductPhysicalCount $physicalCount = null): ProductPhysicalCount
    {
        $isDraft = $request->input('status') === 'draft';

        $request->validate([
            'department_id' => 'required',
            'remarks' => 'nullable|string',
            'pr_items' => $isDraft ? 'nullable|array' : 'required|array|min:1',
            'pr_items.*.product_id' => $isDraft ? 'nullable' : 'required',
            'pr_items.*.quantity' => 'required_with:pr_items.*.product_id',
            'pr_items.*.uom_id' => 'required_with:pr_items.*.product_id',
        ], [
            'pr_items' => 'Product is required',
            'pr_items.*.product_id' => 'Product is required',
            'pr_items.*.quantity' => 'Quantity field required',
            'pr_items.*.uom_id' => 'The product you selected has no UOM. Please assign a UOM first before continuing',
        ]);

        $items = $this->prepareItems($request->input('pr_items', []));

        if (!$isDraft && count($items) === 0) {
            throw ValidationException::withMessages([
                'pr_items' => 'Product is required',
            ]);
        }

        return DB::transaction(function () use ($request, $branch, $physicalCount, $isDraft, $items) {
            if (!$physicalCount) {
                $physicalCount = new ProductPhysicalCount();
                $physicalCount->branch_id = $branch->id;
                $physicalCount->action_by = auth()->user()->id;
                $physicalCount->pcount_number = $this->generatePcountNumber($branch);
            }

            $physicalCount->department_id = $request->department_id;
            $physicalCount->remarks = $request->remarks;
            $physicalCount->status = $isDraft ? 'draft' : 'pending';
            $physicalCount->save();

            $physicalCount->items()->delete();

            if ($items) {
                $physicalCount->items()->createMany($items);
            }

            return $physicalCount;
        });
    }

    private function userOwnsDraft(ProductPhysicalCount $count): bool
    {
        return (int) $count->created_by === (int) auth()->id();
    }

    private function prepareItems(array $items): array
    {
        $prepared = [];

        foreach ($items as $item) {
            if (empty($item['product_id'])) {
                continue;
            }

            $prepared[] = [
                'product_id' => $item['product_id'],
                'uom_id' => $item['uom_id'],
                'quantity' => $item['quantity'],
                'remarks' => $item['remarks'] ?? null,
            ];
        }

        return $prepared;
    }

    private function generatePcountNumber($branch): string
    {
        $pcountCount = ProductPhysicalCount::where([
            'branch_id' => $branch->id
        ])->count();

        $branchCode = strtoupper($branch->code);
        $date = date('Ymd');
        $counter = str_pad($pcountCount + 1, 4, '0', STR_PAD_LEFT);

        return "PCOUNT{$branchCode}{$date}{$counter}";
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
