<?php

namespace App\Http\Controllers\Branch;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\DebitMemoItem;
use App\Models\PurchaseOrder;
use App\Models\PurchaseReturn;
use App\Repositories\Interfaces\ProductRepositoryInterface;
use App\Services\FifoCostService;
use App\Services\ThreeWayMatchService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PurchaseReturnController extends Controller
{
    public function __construct(
        private ProductRepositoryInterface $productRepository,
        private ThreeWayMatchService $matchService,
        private FifoCostService $fifoCost
    ) {
    }

    public function index(Request $request, string $companySlug, string $branchSlug)
    {
        $company = $request->attributes->get('company');
        $branch = $request->attributes->get('branch');

        $returns = PurchaseReturn::with(['supplier', 'purchaseOrder', 'createdBy'])
            ->where('branch_id', $branch->id)
            ->when($request->query('status'), function ($query, $status) {
                $query->where('status', $status);
            })
            ->when($request->query('search'), function ($query, $search) {
                $query->where(function ($query) use ($search) {
                    $query->where('return_number', 'like', '%' . $search . '%')
                        ->orWhereHas('purchaseOrder', function ($query) use ($search) {
                            $query->where('po_number', 'like', '%' . $search . '%');
                        })
                        ->orWhereHas('supplier', function ($query) use ($search) {
                            $query->where('name', 'like', '%' . $search . '%');
                        });
                });
            })
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        return view('branch.purchaseReturns.index', [
            'company' => $company,
            'branch' => $branch,
            'returns' => $returns,
        ]);
    }

    public function create(Request $request, string $companySlug, string $branchSlug)
    {
        $company = $request->attributes->get('company');
        $branch = $request->attributes->get('branch');
        $orders = $this->returnableOrders($branch->id);
        $purchaseOrder = null;
        $lines = [];

        if ($request->query('purchase_order_id')) {
            $purchaseOrder = $orders->firstWhere('id', (int) $request->query('purchase_order_id'));
            if ($purchaseOrder) {
                $summary = $this->matchService->purchaseOrderSummary($purchaseOrder);
                foreach ($purchaseOrder->items as $item) {
                    $returnable = $summary[$item->id]['received'] - $summary[$item->id]['returned'];
                    if ($returnable > 0) {
                        $lines[] = [
                            'item' => $item,
                            'returnable' => $returnable,
                            'received' => $summary[$item->id]['received'],
                        ];
                    }
                }
            }
        }

        return view('branch.purchaseReturns.create', [
            'company' => $company,
            'branch' => $branch,
            'orders' => $orders,
            'purchaseOrder' => $purchaseOrder,
            'lines' => $lines,
        ]);
    }

    public function store(Request $request, string $companySlug, string $branchSlug)
    {
        $branch = $request->attributes->get('branch');
        $company = $request->attributes->get('company');

        $validated = $request->validate([
            'purchase_order_id' => 'required|integer',
            'return_date' => 'required|date',
            'remarks' => 'nullable|string',
            'items' => 'required|array',
            'items.*.qty' => 'nullable|numeric|min:0',
            'items.*.reason' => 'nullable|string',
        ]);

        $return = DB::transaction(function () use ($branch, $validated) {
            $po = PurchaseOrder::where('branch_id', $branch->id)
                ->where('status', 'approved')
                ->lockForUpdate()
                ->find($validated['purchase_order_id']);

            if (!$po) {
                throw ValidationException::withMessages([
                    'purchase_order_id' => 'Select an approved purchase order for this branch.',
                ]);
            }

            $po->load('items.product', 'items.uom');
            $summary = $this->matchService->purchaseOrderSummary($po);
            $lines = [];

            foreach ($validated['items'] as $poItemId => $payload) {
                $qty = round((float) ($payload['qty'] ?? 0), 4);
                if ($qty <= 0) {
                    continue;
                }

                $poItem = $po->items->firstWhere('id', (int) $poItemId);
                $returnable = $poItem
                    ? round(($summary[$poItem->id]['received'] ?? 0) - ($summary[$poItem->id]['returned'] ?? 0), 4)
                    : 0;

                if (!$poItem || $qty > $returnable) {
                    throw ValidationException::withMessages([
                        'items' => 'Return quantity is higher than the quantity received and not yet returned.',
                    ]);
                }

                $lines[] = [
                    'purchase_order_item_id' => $poItem->id,
                    'product_id' => $poItem->product_id,
                    'uom_id' => $poItem->uom_id,
                    'qty' => $qty,
                    'unit_price' => $poItem->unit_price,
                    'reason' => $payload['reason'] ?? null,
                ];
            }

            if (!$lines) {
                throw ValidationException::withMessages([
                    'items' => 'Enter a return quantity for at least one item.',
                ]);
            }

            $purchaseReturn = PurchaseReturn::create([
                'branch_id' => $branch->id,
                'supplier_id' => $po->supplier_id,
                'purchase_order_id' => $po->id,
                'return_number' => $this->nextNumber($branch),
                'return_date' => $validated['return_date'],
                'remarks' => $validated['remarks'] ?? null,
                'status' => 'pending',
            ]);
            $purchaseReturn->items()->createMany($lines);

            return $purchaseReturn;
        });

        return redirect()
            ->route('branch.purchase-returns.show', [
                'companySlug' => $company->slug,
                'branchSlug' => $branch->slug,
                'id' => $return->id,
            ])
            ->with('success', 'Purchase return saved.');
    }

    public function show(Request $request, string $companySlug, string $branchSlug, string $id)
    {
        $company = $request->attributes->get('company');
        $branch = $request->attributes->get('branch');
        $purchaseReturn = $this->findReturn($branch->id, $id);

        return view('branch.purchaseReturns.show', [
            'company' => $company,
            'branch' => $branch,
            'purchaseReturn' => $purchaseReturn,
        ]);
    }

    public function approve(Request $request, string $companySlug, string $branchSlug, string $id)
    {
        $branch = $request->attributes->get('branch');
        $purchaseReturn = $this->findReturn($branch->id, $id);

        DB::transaction(function () use ($purchaseReturn, $branch) {
            $locked = PurchaseReturn::where('id', $purchaseReturn->id)->lockForUpdate()->first();
            if ($locked->status !== 'pending') {
                throw ValidationException::withMessages([
                    'status' => 'This return can no longer be approved.',
                ]);
            }

            PurchaseOrder::where('id', $locked->purchase_order_id)->lockForUpdate()->first();
            $po = PurchaseOrder::with('items')->find($locked->purchase_order_id);
            $summary = $this->matchService->purchaseOrderSummary($po);
            $locked->load('items.product.branches');

            foreach ($locked->items as $item) {
                $returnable = round(($summary[$item->purchase_order_item_id]['received'] ?? 0) - ($summary[$item->purchase_order_item_id]['returned'] ?? 0), 4);
                if (round((float) $item->qty, 4) > $returnable) {
                    throw ValidationException::withMessages([
                        'status' => 'Return quantity is higher than the quantity received and not yet returned.',
                    ]);
                }
            }

            foreach ($locked->items as $item) {
                $item->product->unsetRelation('branches');
                $moved = $this->productRepository->updateBranchQuantity(
                    $item->product,
                    $branch,
                    $locked->id,
                    'purchase_returns',
                    (int) round((float) $item->qty),
                    null,
                    'subtract',
                    $item->uom_id
                );
                if (!$moved) {
                    throw ValidationException::withMessages([
                        'status' => 'Stock for this return was already posted.',
                    ]);
                }

                $this->fifoCost->consume($branch, $item->product, $item->qty, $item->uom_id, 'purchase_returns', $locked->id);
            }

            $locked->status = 'approved';
            $locked->action_by = auth()->user()->id;
            $locked->save();
        });

        return redirect()->back()->with('success', 'Purchase return approved. Stock has been reduced.');
    }

    public function reject(Request $request, string $companySlug, string $branchSlug, string $id)
    {
        $branch = $request->attributes->get('branch');
        $purchaseReturn = $this->findReturn($branch->id, $id);

        if ($purchaseReturn->status !== 'pending') {
            throw ValidationException::withMessages([
                'status' => 'This return can no longer be rejected.',
            ]);
        }

        $purchaseReturn->status = 'rejected';
        $purchaseReturn->action_by = auth()->user()->id;
        $purchaseReturn->save();

        return redirect()->back()->with('success', 'Purchase return rejected.');
    }

    public function void(Request $request, string $companySlug, string $branchSlug, string $id)
    {
        $branch = $request->attributes->get('branch');
        $purchaseReturn = $this->findReturn($branch->id, $id);

        DB::transaction(function () use ($purchaseReturn, $branch) {
            $locked = PurchaseReturn::where('id', $purchaseReturn->id)->lockForUpdate()->first();
            if ($locked->status !== 'approved') {
                throw ValidationException::withMessages([
                    'status' => 'Only an approved return can be voided.',
                ]);
            }

            $locked->load('items.product.branches');
            $linked = DebitMemoItem::whereIn('purchase_return_item_id', $locked->items->pluck('id'))
                ->whereHas('debitMemo', function ($query) {
                    $query->where('status', '!=', 'void');
                })
                ->exists();

            if ($linked) {
                throw ValidationException::withMessages([
                    'status' => 'Void the debit memo before voiding this return.',
                ]);
            }

            foreach ($locked->items as $item) {
                $item->product->unsetRelation('branches');
                $moved = $this->productRepository->updateBranchQuantity(
                    $item->product,
                    $branch,
                    $locked->id,
                    'purchase_returns_void',
                    (int) round((float) $item->qty),
                    null,
                    'add',
                    $item->uom_id
                );
                if (!$moved) {
                    throw ValidationException::withMessages([
                        'status' => 'Stock for this return was already restored.',
                    ]);
                }

                $this->fifoCost->receiveFromMovement($branch, $item->product, $item->qty, $item->uom_id, 'purchase_returns', $locked->id, 'purchase_returns_void', $locked->id);
            }

            $locked->status = 'void';
            $locked->voided_by = auth()->user()->id;
            $locked->voided_at = now();
            $locked->save();
        });

        return redirect()->back()->with('success', 'Purchase return voided. Stock has been restored.');
    }

    private function returnableOrders(int $branchId)
    {
        return PurchaseOrder::with(['items.product', 'items.uom', 'supplier'])
            ->where('branch_id', $branchId)
            ->where('status', 'approved')
            ->orderByDesc('id')
            ->get()
            ->filter(function (PurchaseOrder $po) {
                $summary = $this->matchService->purchaseOrderSummary($po);

                return collect($summary)->contains(function ($line) {
                    return ($line['received'] - $line['returned']) > 0;
                });
            })
            ->values();
    }

    private function findReturn(int $branchId, string $id): PurchaseReturn
    {
        return PurchaseReturn::with([
            'items.product',
            'items.uom',
            'supplier',
            'purchaseOrder',
            'createdBy',
            'actionBy',
            'voidedBy',
        ])->where('branch_id', $branchId)->findOrFail($id);
    }

    private function nextNumber(Branch $branch): string
    {
        $count = PurchaseReturn::where('branch_id', $branch->id)->count();

        return 'RTN' . strtoupper($branch->code) . date('Ymd') . str_pad($count + 1, 4, '0', STR_PAD_LEFT);
    }
}
