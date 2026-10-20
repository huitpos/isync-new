<?php

namespace App\Http\Controllers\Branch;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

use App\DataTables\Branch\StockTransferDeliveriesDataTable;

use App\Models\StockTransferDelivery;

use App\Repositories\Interfaces\ProductRepositoryInterface;
use App\Services\FifoCostService;

class StockTransferDeliveryController extends Controller
{
    protected $productRepository;
    protected $fifoCost;

    public function __construct(
        ProductRepositoryInterface $productRepository,
        FifoCostService $fifoCost
    ) {
        $this->productRepository = $productRepository;
        $this->fifoCost = $fifoCost;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, StockTransferDeliveriesDataTable $dataTable)
    {
        $company = $request->attributes->get('company');
        $branch = $request->attributes->get('branch');

        return $dataTable->with([
            'branch_id' => $branch->id,
            'branch_slug' => $branch->slug,
            'company_slug' => $company->slug,
        ])->render('branch.stockTransferDeliveries.index', [
            'company' => $company,
            'branch' => $branch,
        ]);
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
        $std = StockTransferDelivery::with([
            'items',
            'createdBy'
        ])->findOrFail($id);

        $company = $request->attributes->get('company');
        $branch = $request->attributes->get('branch');

        return view('branch.stockTransferDeliveries.show', [
            'std' => $std,
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
        $std = StockTransferDelivery::with([
            'items',
        ])->findOrFail($id);

        $company = $request->attributes->get('company');
        $branch = $request->attributes->get('branch');

        foreach ($request->item_id as $key => $item) {
            $stdItem = $std->items->where('id', $item)->first();
            $stdItem->qty = $request->qty[$key];
            $stdItem->save();

        }

        $std->status = $request->status;
        $std->save();

        if ($std->status == 'approved') {
            foreach ($std->items as $item) {
                $product = $item->product;

                $moved = $this->productRepository->updateBranchQuantity($product, $branch, $id, 'stock_transfer_deliveries', $item->qty, null, 'add', $item->uom_id);
                if ($moved) {
                    $this->fifoCost->receiveFromMovement($branch, $product, $item->qty, $item->uom_id, 'stock_transfer_orders', $std->stock_transfer_order_id, 'stock_transfer_deliveries', $id);
                }
            }
        }

        return redirect()->route('branch.stock-transfer-deliveries.index', ['companySlug' => $company->slug, 'branchSlug' => $branch->slug])->with('success', 'Data has been updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    public function print(Request $request, string $companySlug, string $branchSlug, string $id)
    {
        $std = StockTransferDelivery::with([
            'items',
            'createdBy'
        ])->findOrFail($id);

        $company = $request->attributes->get('company');
        $branch = $request->attributes->get('branch');

        $pdf = Pdf::loadView('branch.stockTransferDeliveries.print', [
            'std' => $std,
            'company' => $company,
            'branch' => $branch
        ]);

        return $pdf->download("STD-$std->std_number.pdf");
    }
}
