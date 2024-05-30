<?php

namespace App\Http\Controllers\Branch;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\DataTables\Branch\StockTransferOrdersDataTable;

use App\Models\StockTransferOrder;
use App\Models\StockTransferDelivery;

use App\Repositories\Interfaces\ProductRepositoryInterface;

class StockTransferOrderController extends Controller
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
    public function index(Request $request, StockTransferOrdersDataTable $dataTable)
    {
        $company = $request->attributes->get('company');
        $branch = $request->attributes->get('branch');

        return $dataTable->with([
            'branch_id' => $branch->id,
            'branch_slug' => $branch->slug,
            'company_slug' => $company->slug,
        ])->render('branch.stockTransferOrders.index', [
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
        $sto = StockTransferOrder::with([
            'items',
            'createdBy'
        ])->findOrFail($id);

        $company = $request->attributes->get('company');
        $branch = $request->attributes->get('branch');

        return view('branch.stockTransferOrders.show', [
            'sto' => $sto,
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
        $sto = StockTransferOrder::with([
            'items',
        ])->findOrFail($id);

        $company = $request->attributes->get('company');
        $branch = $request->attributes->get('branch');

        foreach ($request->item_id as $key => $item) {
            $stoItem = $sto->items->where('id', $item)->first();
            $stoItem->quantity = $request->quantity[$key];
            $stoItem->save();

        }

        $sto->status = $request->status;
        $sto->save();

        if ($sto->status == 'approved') {
            foreach ($sto->items as $item) {
                $product = $item->product;

                $this->productRepository->updateBranchQuantity($product, $branch, $id, 'stock_transfer_orders', $item->quantity, null, 'subtract', $item->uom_id);
            }

            $sto = StockTransferOrder::with([
                'items',
            ])->findOrFail($id);

            $stdCount = StockTransferDelivery::where([
                'destination_branch_id' => $branch->id
            ])->count();

            $branchCode = strtoupper($branch->branch_code);
            $date = date('Ymd');
            $counter = str_pad($stdCount+1, 4, '0', STR_PAD_LEFT);
            $stdNumber = "STD$branchCode$date$counter";

            $poData = [
                'source_branch_id' => $sto->source_branch_id,
                'destination_branch_id' => $sto->destination_branch_id,
                'stock_transfer_order_id' => $sto->id,
                'std_number' => $stdNumber,
                'status' => 'pending',
                'action_by' => auth()->user()->id,
                'delivery_number' => '',
            ];

            $stockTransferDelivery = new StockTransferDelivery();
            $stockTransferDelivery->fill($poData);
            $stockTransferDelivery->save();

            $stdItems = [];
            foreach ($sto->items as $item) {
                $stdItems[] = [
                    'stock_transfer_order_item_id' => $item->id,
                    'product_id' => $item->product_id,
                    'uom_id' => $item->uom_id,
                    'qty' => $item->quantity,
                ];
            }

            $stockTransferDelivery->items()->createMany($stdItems);
        }

        return redirect()->route('branch.stock-transfer-orders.index', ['companySlug' => $company->slug, 'branchSlug' => $branch->slug])->with('success', 'Data has been updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}