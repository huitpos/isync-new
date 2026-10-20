<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\DataTables\Company\DebitMemosDataTable;
use App\Models\DebitMemo;
use App\Models\SupplierInvoice;
use App\Services\ThreeWayMatchService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class DebitMemoController extends Controller
{
    public function __construct(private ThreeWayMatchService $matchService)
    {
    }

    public function index(Request $request, string $companySlug, DebitMemosDataTable $dataTable)
    {
        $company = $request->attributes->get('company');

        return $dataTable->with([
            'company_id' => $company->id,
            'company_slug' => $company->slug,
        ])->render('company.debitMemos.index', [
            'company' => $company,
            'branches' => $company->branches,
        ]);
    }

    public function create(Request $request, string $companySlug = '')
    {
        $company = $request->attributes->get('company');
        $invoices = SupplierInvoice::with(['supplier', 'purchaseOrder', 'branch'])
            ->where('status', 'accepted')
            ->whereHas('branch', function ($query) use ($company) {
                $query->where('company_id', $company->id);
            })
            ->orderByDesc('id')
            ->get();

        $invoice = null;
        $rows = [];
        if ($request->query('supplier_invoice_id')) {
            $invoice = $invoices->firstWhere('id', (int) $request->query('supplier_invoice_id'));
            if ($invoice) {
                $invoice->load('items.product', 'items.uom', 'purchaseOrder.items');
                $remaining = $this->matchService->invoiceDebitRemaining($invoice);
                $returnLines = collect($this->matchService->returnDebitOpen($invoice->purchaseOrder))
                    ->filter(fn ($line) => $line['open'] > 0)
                    ->groupBy('purchase_order_item_id');

                foreach ($invoice->items as $item) {
                    $rows[] = [
                        'item' => $item,
                        'remaining' => $remaining[$item->id]['amount'] ?? 0,
                        'returns' => $returnLines->get($item->purchase_order_item_id, collect())->values(),
                    ];
                }
            }
        }

        return view('company.debitMemos.create', [
            'company' => $company,
            'invoices' => $invoices,
            'invoice' => $invoice,
            'rows' => $rows,
        ]);
    }

    public function store(Request $request, string $companySlug)
    {
        $company = $request->attributes->get('company');
        $validated = $request->validate([
            'supplier_invoice_id' => 'required|integer',
            'memo_date' => 'required|date',
            'remarks' => 'nullable|string',
            'items' => 'required|array',
            'items.*.qty' => 'nullable|numeric|min:0',
            'items.*.unit_price' => 'nullable|numeric|min:0',
            'items.*.purchase_return_item_id' => 'nullable|integer',
        ]);

        $memo = DB::transaction(function () use ($company, $validated) {
            $invoice = SupplierInvoice::with(['items.product', 'purchaseOrder.branch', 'branch'])
                ->where('status', 'accepted')
                ->whereHas('branch', function ($query) use ($company) {
                    $query->where('company_id', $company->id);
                })
                ->lockForUpdate()
                ->find($validated['supplier_invoice_id']);

            if (!$invoice) {
                throw ValidationException::withMessages([
                    'supplier_invoice_id' => 'Select an accepted supplier invoice.',
                ]);
            }

            $remaining = $this->matchService->invoiceDebitRemaining($invoice);
            $returnLines = $this->matchService->returnDebitOpen($invoice->purchaseOrder);
            $usedReturns = [];
            $lines = [];

            foreach ($validated['items'] as $invoiceItemId => $payload) {
                $qty = round((float) ($payload['qty'] ?? 0), 4);
                if ($qty <= 0) {
                    continue;
                }

                $invoiceItem = $invoice->items->firstWhere('id', (int) $invoiceItemId);
                if (!$invoiceItem) {
                    throw ValidationException::withMessages([
                        'items' => 'An item does not belong to this supplier invoice.',
                    ]);
                }

                $returnItemId = (int) ($payload['purchase_return_item_id'] ?? 0);
                if ($returnItemId) {
                    $returnLine = $returnLines[$returnItemId] ?? null;
                    if (!$returnLine || (int) $returnLine['purchase_order_item_id'] !== (int) $invoiceItem->purchase_order_item_id) {
                        throw ValidationException::withMessages([
                            'items' => 'The purchase return does not match this invoice item.',
                        ]);
                    }
                    if (isset($usedReturns[$returnItemId]) || $qty > $returnLine['open']) {
                        throw ValidationException::withMessages([
                            'items' => 'Debit quantity is higher than the returned quantity that has been billed.',
                        ]);
                    }
                    $usedReturns[$returnItemId] = true;
                    $unitPrice = $returnLine['unit_price'];
                } else {
                    if (!isset($payload['unit_price']) || $payload['unit_price'] === '') {
                        throw ValidationException::withMessages([
                            'items' => 'The debit price is required.',
                        ]);
                    }
                    $unitPrice = round((float) $payload['unit_price'], 4);
                }

                $lineAmount = round($qty * $unitPrice, 4);
                if ($lineAmount <= 0) {
                    throw ValidationException::withMessages([
                        'items' => 'The debit amount must be greater than zero.',
                    ]);
                }
                $openAmount = $remaining[$invoiceItem->id]['amount'] ?? 0;
                if ($lineAmount > $openAmount) {
                    throw ValidationException::withMessages([
                        'items' => 'Debit amount is higher than the remaining amount of the invoice line.',
                    ]);
                }
                $remaining[$invoiceItem->id]['amount'] = round($openAmount - $lineAmount, 4);

                $lines[] = [
                    'supplier_invoice_item_id' => $invoiceItem->id,
                    'purchase_return_item_id' => $returnItemId ?: null,
                    'product_id' => $invoiceItem->product_id,
                    'uom_id' => $invoiceItem->uom_id,
                    'qty' => $qty,
                    'unit_price' => $unitPrice,
                    'line_amount' => $lineAmount,
                ];
            }

            if (!$lines) {
                throw ValidationException::withMessages([
                    'items' => 'Enter a debit quantity for at least one item.',
                ]);
            }

            $total = round(collect($lines)->sum('line_amount'), 4);
            $memo = DebitMemo::create([
                'branch_id' => $invoice->branch_id,
                'supplier_id' => $invoice->supplier_id,
                'supplier_invoice_id' => $invoice->id,
                'memo_number' => $this->nextNumber($invoice->branch),
                'memo_date' => $validated['memo_date'],
                'total_amount' => $total,
                'remarks' => $validated['remarks'] ?? null,
                'status' => 'issued',
            ]);
            $memo->items()->createMany($lines);

            return $memo;
        });

        return redirect()
            ->route('company.debit-memos.show', [
                'companySlug' => $company->slug,
                'id' => $memo->id,
            ])
            ->with('success', 'Debit memo issued.');
    }

    public function show(Request $request, string $companySlug, string $id)
    {
        $company = $request->attributes->get('company');
        $memo = $this->findMemo($company->id, $id);

        return view('company.debitMemos.show', [
            'company' => $company,
            'memo' => $memo,
        ]);
    }

    public function void(Request $request, string $companySlug, string $id)
    {
        $company = $request->attributes->get('company');
        $memo = $this->findMemo($company->id, $id);

        if ($memo->status !== 'issued') {
            throw ValidationException::withMessages([
                'status' => 'This debit memo is already void.',
            ]);
        }

        $memo->status = 'void';
        $memo->voided_by = auth()->user()->id;
        $memo->voided_at = now();
        $memo->save();

        return redirect()->back()->with('success', 'Debit memo voided.');
    }

    private function findMemo(int $companyId, string $id): DebitMemo
    {
        return DebitMemo::with([
            'items.product',
            'items.uom',
            'items.purchaseReturnItem.purchaseReturn',
            'supplier',
            'supplierInvoice.purchaseOrder',
            'branch',
            'createdBy',
            'voidedBy',
        ])->whereHas('branch', function ($query) use ($companyId) {
            $query->where('company_id', $companyId);
        })->findOrFail($id);
    }

    private function nextNumber(Branch $branch): string
    {
        $count = DebitMemo::where('branch_id', $branch->id)->count();

        return 'DM' . strtoupper($branch->code) . date('Ymd') . str_pad($count + 1, 4, '0', STR_PAD_LEFT);
    }
}
