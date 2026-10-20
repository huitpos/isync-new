<?php

namespace App\Http\Controllers\Branch;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

use App\Models\PurchaseOrder;
use App\Models\PurchaseDelivery;
use App\Models\Branch;

use App\DataTables\Branch\PurchaseDeliveriesDataTable;

use App\Repositories\Interfaces\ProductRepositoryInterface;
use App\Services\FifoCostService;
use App\Services\Gl\DocumentJournalService;
use App\Services\ThreeWayMatchService;

use Barryvdh\DomPDF\Facade\Pdf;

class PurchaseDeliveryController extends Controller
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
    public function index(Request $request, PurchaseDeliveriesDataTable $dataTable)
    {
        $company = $request->attributes->get('company');
        $branch = $request->attributes->get('branch');
        $search = $request->query('search', null);

        return $dataTable->with([
            'branch_id' => $branch->id,
            'branch_slug' => $branch->slug,
            'company_slug' => $company->slug,
            'status' => $request->input('status', null),
            'search' => $search,
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
            'sales_invoice_number' => 'nullable|string|max:255',
            'delivery_number' => 'required',
            'items' => 'required|array',
            'items.*.unit_price' => 'nullable|numeric|min:0',
            'items.*.qty' => 'nullable|numeric|min:0',
        ]);

        $po = PurchaseOrder::with('items')->where('branch_id', $branch->id)->findOrFail($request->purchase_order_id);

        DB::transaction(function () use ($request, $po, $branch) {
            $lines = [];
            $totalQty = 0;
            $totalAmount = 0;

            foreach ($request->items as $index => $item) {
                $poItem = $po->items->firstWhere('id', $item['purchase_order_item_id'] ?? null);
                if (!$poItem) {
                    throw ValidationException::withMessages([
                        'items' => 'A line does not belong to this purchase order.',
                    ]);
                }

                $qty = round((float) ($item['qty'] ?? 0), 4);
                if ($qty <= 0) {
                    continue;
                }

                $balance = round((float) $poItem->balance, 4);
                if ($qty > $balance) {
                    throw ValidationException::withMessages([
                        "items.$index.qty" => 'Receive quantity cannot be more than the balance of ' . $balance . '.',
                    ]);
                }

                if (!isset($item['unit_price']) || $item['unit_price'] === '') {
                    throw ValidationException::withMessages([
                        "items.$index.unit_price" => 'Unit price is required when receiving a quantity.',
                    ]);
                }

                $unitPrice = round((float) $item['unit_price'], 4);
                $poItem->balance = round($balance - $qty, 4);
                $poItem->save();

                $totalQty += $qty;
                $totalAmount += $qty * $unitPrice;
                $lines[] = [
                    'purchase_order_item_id' => $poItem->id,
                    'product_id' => $poItem->product_id,
                    'uom_id' => $poItem->uom_id,
                    'qty' => $qty,
                    'unit_price' => $unitPrice,
                    'po_unit_price' => $poItem->unit_price,
                ];
            }

            if (!$lines) {
                throw ValidationException::withMessages([
                    'items' => 'Enter a receive quantity for at least one item.',
                ]);
            }

            if ($request->is_closed == '1') {
                $po->is_closed = 1;
                $po->save();
            }

            $poCount = PurchaseDelivery::where([
                'branch_id' => $branch->id
            ])->count();

            $branchCode = strtoupper($branch->code);
            $date = date('Ymd');
            $counter = str_pad($poCount + 1, 4, '0', STR_PAD_LEFT);
            $pdNumber = "MRI$branchCode$date$counter";

            $pd = PurchaseDelivery::create([
                'purchase_order_id' => $po->id,
                'sales_invoice_number' => $request->sales_invoice_number ?: null,
                'delivery_number' => $request->delivery_number,
                'total_qty' => $totalQty,
                'total_amount' => round($totalAmount, 4),
                'pd_number' => $pdNumber,
                'branch_id' => $branch->id,
            ]);

            $pd->items()->createMany($lines);
        });

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
        $billingStatus = app(ThreeWayMatchService::class)->deliveryBillingStatus($pd);

        return view('branch.purchaseDeliveries.show', compact('pd', 'company', 'branch', 'billingStatus'));
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

        DB::transaction(function () use ($pd, $branch, $status, $id) {
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

                    $srp = $product->markup_type == 'percentage' ? $item->unit_price + ($item->unit_price * ($product->markup / 100)) : $item->unit_price + $product->markup;

                    $product->srp = $srp;
                    $product->save();

                    $moved = $this->productRepository->updateBranchQuantity($product, $branch, $id, 'purchase_deliveries', $item->qty, $srp, 'add', $item->uom_id);
                    if ($moved) {
                        $this->fifoCost->receiveDocument($branch, $product, $item->qty, $item->uom_id, (float) $item->unit_price, 'purchase_deliveries', $id);
                    }
                }

                app(DocumentJournalService::class)->postPurchaseDelivery($pd->fresh(['items', 'branch']));
            }
        });

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
