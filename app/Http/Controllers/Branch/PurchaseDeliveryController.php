<?php

namespace App\Http\Controllers\Branch;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\PurchaseOrder;
use App\Models\PurchaseDelivery;
use App\Models\Branch;

use App\DataTables\Branch\PurchaseDeliveriesDataTable;

use App\Repositories\Interfaces\ProductRepositoryInterface;

use Barryvdh\DomPDF\Facade\Pdf;

class PurchaseDeliveryController extends Controller
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
    public function index(Request $request, PurchaseDeliveriesDataTable $dataTable)
    {
        $company = $request->attributes->get('company');
        $branch = $request->attributes->get('branch');

        return $dataTable->with([
            'branch_id' => $branch->id,
            'branch_slug' => $branch->slug,
            'company_slug' => $company->slug,
            'status' => $request->query('status', null),
        ])->render('branch.purchaseDeliveries.index', [
            'company' => $company,
            'branch' => $branch,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request, string $companySlug, string $branchSlug, string $purchaseOrderId)
    {
        $po = PurchaseOrder::with('items')->findOrFail($purchaseOrderId);
        $company = $request->attributes->get('company');
        $branch = $request->attributes->get('branch');

        return view('branch.purchaseDeliveries.create', compact('po', 'company', 'branch'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, string $companySlug, string $branchSlug)
    {
        $company = $request->attributes->get('company');
        $branch = $request->attributes->get('branch');

        $request->validate([
            'purchase_order_id' => 'required|exists:purchase_orders,id',
            'sales_invoice_number' => 'required',
            'delivery_number' => 'required',
            'items.*.unit_price' => 'required',
            'items.*.qty' => 'required',
        ], [
            'items.*.unit_price.required' => 'The unit price field is required.',
            'items.*.qty.required' => 'The quantity field is required.',
        ]);

        // update PO
        $po = PurchaseOrder::with('items')->findOrFail($request->purchase_order_id);
        $po->total = $request->grandtotal;
        if ($request->is_closed == "1") {
            $po->is_closed = 1;
        };
        $po->save();

        //update PO items
        foreach ($request->items as $item) {
            $poItem = $po->items->where('id', $item['purchase_order_item_id'])->first();
            $poItem->balance = $item['balance'];
            $poItem->unit_price = $item['unit_price'];
            $poItem->save();
        }

        $poCount = PurchaseDelivery::where([
            'branch_id' => $branch->id
        ])->count();

        $branchCode = strtoupper($branch->code);
        $date = date('Ymd');
        $counter = str_pad($poCount+1, 4, '0', STR_PAD_LEFT);
        $pdNumber = "MRI$branchCode$date$counter";

        $pdData = [
            'purchase_order_id' => $request->purchase_order_id,
            'sales_invoice_number' => $request->sales_invoice_number,
            'delivery_number' => $request->delivery_number,
            'total_qty' => $request->total_qty,
            'total_amount' => $request->total_amount,
            'pd_number' => $pdNumber,
            'branch_id' => $branch->id,
        ];

        $pd = PurchaseDelivery::create($pdData);

        $pd->items()->createMany($request->items);

        return redirect()->route('branch.purchase-deliveries.index', ['companySlug' => $company->slug, 'branchSlug' => $branch->slug])->with('success', 'Data has been updated successfully!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request,string $companySlug, string $branchSlug, string $id)
    {
        $pd = PurchaseDelivery::with('items', 'purchaseOrder')->findOrFail($id);

        $company = $request->attributes->get('company');
        $branch = $request->attributes->get('branch');

        return view('branch.purchaseDeliveries.show', compact('pd', 'company', 'branch'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {

    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request,string $companySlug, string $branchSlug, string $id)
    {
        $pd = PurchaseDelivery::findOrFail($id);

        $branch = Branch::findOrFail($pd->branch_id);

        $status = $request->input('status');
        $pd->status = $status;
        $pd->action_by = auth()->user()->id;
        $pd->save();

        if ($status == 'rejected') {
            foreach ($pd->items as $item) {
                $poItem = $item->purchaseOrderItem;
                $poItem->balance = $poItem->balance + $item->qty;
                $poItem->save();
            }

            $pd->purchaseOrder->is_closed = 0;
            $pd->purchaseOrder->save();
        } else {
            foreach ($pd->items as $item) {
                $product = $item->product;

                $product->cost = $item->unit_price;

                $srp = $product->markup_type == 'percentage' ? $item->unit_price + ($item->unit_price * ($product->markup / 100)) : $item->unit_price + $product->markup;

                $product->srp = $srp;
                $product->save();

                $this->productRepository->updateBranchQuantity($product, $branch, $id, 'purchase_deliveries', $item->qty, $srp, 'add', $item->uom_id);
            }
        }

        return redirect()->back()->with('success', 'Purchase delivery status updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    public function print(Request $request, string $slug, string $branchSlug, string $id)
    {
        $pr = PurchaseDelivery::with([
            'items',
            'createdBy'
        ])->findOrFail($id);

        $company = $request->attributes->get('company');

        $pdf = Pdf::loadView('company.purchaseDeliveries.print', [
            'pr' => $pr,
            'company' => $company,
            'branch' => $pr->branch
        ]);

        return $pdf->download("PD-$pr->pd_number.pdf");
    }
}
