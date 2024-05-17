<?php

namespace App\Http\Controllers\Branch;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\ProductDisposal;

use App\DataTables\Branch\ProductDisposalsDataTable;

use App\Repositories\Interfaces\ProductRepositoryInterface;

class ProductDisposalController extends Controller
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
    public function index(Request $request, ProductDisposalsDataTable $dataTable)
    {
        $company = $request->attributes->get('company');
        $branch = $request->attributes->get('branch');

        return $dataTable->with([
            'branch_id' => $branch->id,
            'branch_slug' => $branch->slug,
            'company_slug' => $company->slug,
            'status' => $request->query('status', null),
        ])->render('branch.productDisposals.index', [
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

        $reasons = $company->productDisposalReasons;

        return view('branch.productDisposals.create', [
            'company' => $company,
            'branch' => $branch,
            'departments' => $departments,
            'reasons' => $reasons
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

        $disposalsData = $request->all();
        $disposalsData['branch_id'] = $branch->id;
        $disposalsData['action_by'] = auth()->user()->id;
        unset($disposalsData['pr_items']);

        //save the purchase request and its items using model
        $productDisposal = new ProductDisposal();
        $productDisposal->fill($disposalsData);
        $productDisposal->save();

        $productDisposal->items()->createMany($postData['pr_items']);

        return redirect()->route('branch.product-disposals.index', ['companySlug' => $company->slug, 'branchSlug' => $branch->slug])->with('success', 'Product disposals has been created.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, string $companySlug, string $branchSlug, string $id)
    {
        $disposal = ProductDisposal::with([
            'items',
            'productDisposalReason',
            'createdBy'
        ])->findOrFail($id);

        $company = $request->attributes->get('company');
        $branch = $request->attributes->get('branch');

        return view('branch.productDisposals.show', [
            'disposal' => $disposal,
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
        $disposal = ProductDisposal::with([
            'items',
            'createdBy'
        ])->findOrFail($id);

        $disposal->status = $request->status;
        $disposal->save();

        $company = $request->attributes->get('company');
        $branch = $request->attributes->get('branch');

        if ($disposal->status == 'approved') {
            foreach ($disposal->items as $item) {
                $product = $item->product;

                $this->productRepository->updateBranchQuantity($product, $branch, $id, 'product_disposals', $item->quantity, null, 'subtract');
            }
        }

        return redirect()->route('branch.product-disposals.index', ['companySlug' => $company->slug, 'branchSlug' => $branch->slug])->with('success', 'Data has been updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
