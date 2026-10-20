<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\PaymentTerm;
use App\Models\PurchaseOrder;
use App\Models\PurchaseRequest;
use App\Models\PurchaseRequestQuotationItem;
use App\Models\Supplier;
use App\Models\SupplierTerm;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PurchaseRequestQuotationController extends Controller
{
    public function show(Request $request, string $companySlug, string $id)
    {
        $company = $request->attributes->get('company');
        $pr = $this->findRequest($company->id, $id);

        $quotedSupplierIds = $pr->quotations->pluck('supplier_id');
        $suppliers = Supplier::where('company_id', $company->id)
            ->where('status', 'active')
            ->whereNotIn('id', $quotedSupplierIds)
            ->orderBy('name')
            ->get();

        $paymentTerms = $company->paymentTerms()->where('status', 'active')->orderBy('name')->get();
        $supplierTerms = $company->supplierTerms()->where('status', 'active')->orderBy('name')->get();
        $purchaseOrders = PurchaseOrder::where('purchase_request_id', $pr->id)->orderBy('id')->get();

        return view('company.purchaseRequests.quotations', [
            'company' => $company,
            'pr' => $pr,
            'suppliers' => $suppliers,
            'paymentTerms' => $paymentTerms,
            'supplierTerms' => $supplierTerms,
            'purchaseOrders' => $purchaseOrders,
            'lowestPrices' => $this->lowestPrices($pr),
        ]);
    }

    public function store(Request $request, string $companySlug, string $id)
    {
        $company = $request->attributes->get('company');
        $pr = $this->findRequest($company->id, $id);
        $this->assertPending($pr);

        $validated = $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'lead_time_days' => 'nullable|integer|min:0',
            'remarks' => 'nullable|string',
        ]);

        $supplier = Supplier::where('company_id', $company->id)
            ->where('status', 'active')
            ->find($validated['supplier_id']);

        if (!$supplier) {
            throw ValidationException::withMessages([
                'supplier_id' => 'Select an active supplier of this company.',
            ]);
        }

        if ($pr->quotations()->where('supplier_id', $supplier->id)->exists()) {
            throw ValidationException::withMessages([
                'supplier_id' => 'This supplier already has a quotation on this request.',
            ]);
        }

        DB::transaction(function () use ($pr, $supplier, $validated) {
            $quotation = $pr->quotations()->create([
                'supplier_id' => $supplier->id,
                'lead_time_days' => $validated['lead_time_days'] ?? null,
                'remarks' => $validated['remarks'] ?? null,
            ]);

            foreach ($pr->items as $item) {
                $quotation->items()->create([
                    'purchase_request_item_id' => $item->id,
                    'unit_price' => null,
                    'available_qty' => null,
                ]);
            }
        });

        return redirect()->back()->with('success', 'Supplier added. Encode the unit price and available quantity.');
    }

    public function update(Request $request, string $companySlug, string $id)
    {
        $company = $request->attributes->get('company');
        $pr = $this->findRequest($company->id, $id);
        $this->assertPending($pr);

        $validated = $request->validate([
            'quotations' => 'required|array',
            'quotations.*.lead_time_days' => 'nullable|integer|min:0',
            'quotations.*.remarks' => 'nullable|string',
            'quotations.*.items' => 'nullable|array',
            'quotations.*.items.*.unit_price' => 'nullable|numeric|min:0',
            'quotations.*.items.*.available_qty' => 'nullable|numeric|min:0',
        ]);

        DB::transaction(function () use ($pr, $validated) {
            foreach ($validated['quotations'] as $quotationId => $payload) {
                $quotation = $pr->quotations->firstWhere('id', (int) $quotationId);
                if (!$quotation) {
                    continue;
                }

                $quotation->update([
                    'lead_time_days' => $this->blankToNull($payload['lead_time_days'] ?? null),
                    'remarks' => $payload['remarks'] ?? null,
                ]);

                foreach ($payload['items'] ?? [] as $quoteItemId => $itemPayload) {
                    $quoteItem = $quotation->items->firstWhere('id', (int) $quoteItemId);
                    if (!$quoteItem) {
                        continue;
                    }

                    $quoteItem->update([
                        'unit_price' => $this->blankToNull($itemPayload['unit_price'] ?? null),
                        'available_qty' => $this->blankToNull($itemPayload['available_qty'] ?? null),
                    ]);
                }
            }
        });

        return redirect()->back()->with('success', 'Quotations saved.');
    }

    public function award(Request $request, string $companySlug, string $id)
    {
        $company = $request->attributes->get('company');
        $pr = $this->findRequest($company->id, $id);
        $this->assertPending($pr);

        $validated = $request->validate([
            'award_mode' => 'required|in:whole,split',
            'award_supplier_id' => 'required_if:award_mode,whole|nullable|exists:suppliers,id',
            'awards' => 'required_if:award_mode,split|array',
            'awards.*' => 'array',
            'awards.*.*' => 'nullable|numeric|min:0',
            'payment_term_id' => 'required|exists:payment_terms,id',
            'supplier_term_id' => 'required|exists:supplier_terms,id',
        ]);

        if (!PaymentTerm::where('company_id', $company->id)->where('id', $validated['payment_term_id'])->exists()) {
            throw ValidationException::withMessages([
                'payment_term_id' => 'Select a payment term of this company.',
            ]);
        }

        if (!SupplierTerm::where('company_id', $company->id)->where('id', $validated['supplier_term_id'])->exists()) {
            throw ValidationException::withMessages([
                'supplier_term_id' => 'Select a supplier term of this company.',
            ]);
        }

        $poNumbers = DB::transaction(function () use ($pr, $validated) {
            $locked = PurchaseRequest::where('id', $pr->id)->lockForUpdate()->first();
            if ($locked->status !== 'pending') {
                throw ValidationException::withMessages([
                    'award_mode' => 'This request is no longer pending.',
                ]);
            }

            $pr->load([
                'items.product',
                'quotations.supplier',
                'quotations.items',
                'branch',
            ]);

            $linesBySupplier = $this->awardedLines($pr, $validated);
            $poNumbers = [];

            foreach ($linesBySupplier as $supplierId => $lines) {
                $poNumber = $this->nextPoNumber($pr->branch);
                $poTotal = 0;
                foreach ($lines as $line) {
                    $poTotal += $line['total'];
                }

                $purchaseOrder = new PurchaseOrder();
                $purchaseOrder->fill([
                    'branch_id' => $pr->branch_id,
                    'department_id' => $pr->department_id,
                    'delivery_location_id' => $pr->delivery_location_id,
                    'supplier_id' => $supplierId,
                    'payment_term_id' => $validated['payment_term_id'],
                    'supplier_term_id' => $validated['supplier_term_id'],
                    'purchase_request_id' => $pr->id,
                    'po_number' => $poNumber,
                    'date_needed' => $pr->date_needed,
                    'pr_remarks' => $pr->remarks,
                    'total' => $poTotal,
                    'status' => 'approved',
                    'action_by' => auth()->user()->id,
                ]);
                $purchaseOrder->save();
                $purchaseOrder->items()->createMany(array_map(function ($line) {
                    unset($line['quotation_item_id']);

                    return $line;
                }, $lines));

                foreach ($lines as $line) {
                    PurchaseRequestQuotationItem::where('id', $line['quotation_item_id'])->update([
                        'is_awarded' => true,
                        'awarded_qty' => $line['quantity'],
                    ]);
                }

                $poNumbers[] = $poNumber;
            }

            $supplierIds = array_keys($linesBySupplier);
            $pr->update([
                'status' => 'approved',
                'action_by' => auth()->user()->id,
                'payment_term_id' => $validated['payment_term_id'],
                'supplier_term_id' => $validated['supplier_term_id'],
                'supplier_id' => count($supplierIds) === 1 ? $supplierIds[0] : null,
            ]);

            return $poNumbers;
        });

        return redirect()->back()->with('success', 'Awarded. Purchase orders created: ' . implode(', ', $poNumbers) . '.');
    }

    private function findRequest(int $companyId, string $id): PurchaseRequest
    {
        return PurchaseRequest::with([
            'items.product',
            'items.uom',
            'branch',
            'quotations.supplier',
            'quotations.items',
            'supplier',
        ])->whereHas('branch', function ($query) use ($companyId) {
            $query->where('company_id', $companyId);
        })->findOrFail($id);
    }

    private function assertPending(PurchaseRequest $pr): void
    {
        if ($pr->status !== 'pending') {
            throw ValidationException::withMessages([
                'supplier_id' => 'Quotations can only be changed while the request is pending.',
            ]);
        }
    }

    private function blankToNull($value)
    {
        if ($value === '' || $value === null) {
            return null;
        }

        return $value;
    }

    private function lowestPrices(PurchaseRequest $pr): array
    {
        $lowest = [];
        foreach ($pr->items as $item) {
            $prices = [];
            foreach ($pr->quotations as $quotation) {
                $quoteItem = $quotation->items->firstWhere('purchase_request_item_id', $item->id);
                if ($quoteItem && $quoteItem->unit_price !== null && $quoteItem->unit_price > 0) {
                    $prices[] = $quoteItem->unit_price;
                }
            }
            $lowest[$item->id] = $prices ? min($prices) : null;
        }

        return $lowest;
    }

    private function awardedLines(PurchaseRequest $pr, array $validated): array
    {
        $linesBySupplier = $validated['award_mode'] === 'whole'
            ? $this->wholeAwardLines($pr, (int) $validated['award_supplier_id'])
            : $this->splitAwardLines($pr, $validated['awards'] ?? []);

        if (!$linesBySupplier) {
            throw ValidationException::withMessages([
                'award_mode' => 'Enter an award quantity for each item.',
            ]);
        }

        return $linesBySupplier;
    }

    private function wholeAwardLines(PurchaseRequest $pr, int $supplierId): array
    {
        $quotation = $pr->quotations->firstWhere('supplier_id', $supplierId);
        if (!$quotation) {
            throw ValidationException::withMessages([
                'award_supplier_id' => 'That supplier has no quotation on this request.',
            ]);
        }

        $lines = [];
        foreach ($pr->items as $item) {
            $quoteItem = $quotation->items->firstWhere('purchase_request_item_id', $item->id);
            $requested = (float) $item->quantity;
            $available = $this->supplyLimit($quoteItem, $requested);

            if (!$quoteItem || round($available, 4) < round($requested, 4)) {
                throw ValidationException::withMessages([
                    'award_supplier_id' => $quotation->supplier->name . ' cannot supply the full quantity of ' . $item->product->name . '. Split the quantity across suppliers.',
                ]);
            }

            $lines[] = $this->awardLine($quoteItem, $item, $requested);
        }

        return [$quotation->supplier_id => $lines];
    }

    private function splitAwardLines(PurchaseRequest $pr, array $awards): array
    {
        $linesBySupplier = [];

        foreach ($pr->items as $item) {
            $requested = (float) $item->quantity;
            $awarded = 0;

            foreach ($pr->quotations as $quotation) {
                $quoteItem = $quotation->items->firstWhere('purchase_request_item_id', $item->id);
                $qty = round((float) ($awards[$item->id][$quotation->id] ?? 0), 4);
                if ($qty <= 0) {
                    continue;
                }

                $available = $this->supplyLimit($quoteItem, $requested);
                if (!$quoteItem || $qty > round($available, 4)) {
                    throw ValidationException::withMessages([
                        'awards' => 'Award quantity for ' . $quotation->supplier->name . ' on ' . $item->product->name . ' is higher than the available quantity.',
                    ]);
                }

                $linesBySupplier[$quotation->supplier_id][] = $this->awardLine($quoteItem, $item, $qty);
                $awarded += $qty;
            }

            if (round($awarded, 4) !== round($requested, 4)) {
                throw ValidationException::withMessages([
                    'awards' => 'Award quantities for ' . $item->product->name . ' must add up to ' . $this->formatQty($requested) . '.',
                ]);
            }
        }

        return $linesBySupplier;
    }

    private function awardLine($quoteItem, $item, float $quantity): array
    {
        if ($quoteItem->unit_price === null || $quoteItem->unit_price <= 0) {
            throw ValidationException::withMessages([
                'award_mode' => 'Every awarded line needs a unit price. Save the quotations first.',
            ]);
        }

        $total = round($quantity * (float) $quoteItem->unit_price, 4);

        return [
            'quotation_item_id' => $quoteItem->id,
            'product_id' => $item->product_id,
            'uom_id' => $item->uom_id,
            'unit_price' => $quoteItem->unit_price,
            'quantity' => $quantity,
            'balance' => $quantity,
            'total' => $total,
            'pr_remarks' => $item->remarks,
        ];
    }

    private function supplyLimit($quoteItem, float $requested): float
    {
        if (!$quoteItem || $quoteItem->available_qty === null) {
            return $requested;
        }

        return (float) $quoteItem->available_qty;
    }

    private function formatQty(float $quantity): string
    {
        $formatted = rtrim(rtrim(number_format($quantity, 4, '.', ''), '0'), '.');

        return $formatted === '' ? '0' : $formatted;
    }

    private function nextPoNumber($branch): string
    {
        $poCount = PurchaseOrder::where('branch_id', $branch->id)->count();

        return 'PO' . strtoupper($branch->code) . date('Ymd') . str_pad($poCount + 1, 4, '0', STR_PAD_LEFT);
    }
}
