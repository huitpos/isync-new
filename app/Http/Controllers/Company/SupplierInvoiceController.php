<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\DataTables\Company\SupplierInvoicesDataTable;
use App\Models\Branch;
use App\Models\PurchaseOrder;
use App\Models\SupplierInvoice;
use App\Services\ThreeWayMatchService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class SupplierInvoiceController extends Controller
{
    public function __construct(private ThreeWayMatchService $matchService)
    {
    }

    public function index(Request $request, SupplierInvoicesDataTable $dataTable)
    {
        $company = $request->attributes->get('company');

        return $dataTable->with([
            'branch_id' => $request->query('branch_id', null),
            'status' => $request->query('status', null),
            'search' => $request->query('search', null),
            'company_id' => $company->id,
            'company_slug' => $company->slug,
        ])->render('company.supplierInvoices.index', [
            'company' => $company,
            'branches' => $company->branches,
            'permissions' => $request->attributes->get('permissionNames'),
        ]);
    }

    public function create(Request $request, string $companySlug = '')
    {
        $company = $request->attributes->get('company');

        return view('company.supplierInvoices.create', [
            'company' => $company,
            'branches' => $company->branches,
        ]);
    }

    public function store(Request $request, string $companySlug)
    {
        $company = $request->attributes->get('company');

        $po = PurchaseOrder::with(['items', 'supplier', 'branch'])
            ->whereHas('branch', function ($query) use ($company) {
                $query->where('company_id', $company->id);
            })
            ->findOrFail($request->input('purchase_order_id'));

        $request->validate([
            'purchase_order_id' => 'required',
            'invoice_number' => [
                'required',
                'string',
                'max:255',
                Rule::unique('supplier_invoices', 'invoice_number')->where(function ($query) use ($po) {
                    return $query->where('supplier_id', $po->supplier_id)->where('status', '!=', 'void');
                }),
            ],
            'invoice_date' => 'required|date',
            'items' => 'required|array',
            'items.*.purchase_order_item_id' => 'required|integer',
            'items.*.qty' => 'nullable|numeric|min:0',
            'items.*.unit_price' => 'nullable|numeric|min:0',
        ]);

        $lines = collect($request->input('items', []))
            ->filter(fn ($line) => round((float) ($line['qty'] ?? 0), 4) > 0)
            ->values();

        if ($lines->isEmpty()) {
            throw ValidationException::withMessages([
                'items' => 'Enter a billed quantity for at least one item.',
            ]);
        }

        if ($lines->pluck('purchase_order_item_id')->unique()->count() !== $lines->count()) {
            throw ValidationException::withMessages([
                'items' => 'Each purchase order item can only be billed once on this invoice.',
            ]);
        }

        foreach ($lines as $index => $line) {
            if (!isset($line['unit_price']) || $line['unit_price'] === '') {
                throw ValidationException::withMessages([
                    "items.$index.unit_price" => 'The invoice price is required.',
                ]);
            }
        }

        $invoice = $this->matchService->create($po, [
            'invoice_number' => $request->invoice_number,
            'invoice_date' => $request->invoice_date,
        ], $lines->all());

        return redirect()
            ->route('company.supplier-invoices.show', [
                'companySlug' => $company->slug,
                'supplier_invoice' => $invoice->id,
            ])
            ->with('success', 'Supplier invoice saved.');
    }

    public function show(Request $request, string $companySlug, string $id)
    {
        $company = $request->attributes->get('company');
        $invoice = $this->findInvoice($company->id, $id);

        return view('company.supplierInvoices.show', [
            'company' => $company,
            'invoice' => $invoice,
            'permissions' => $request->attributes->get('permissionNames'),
        ]);
    }

    public function openPurchaseOrders(Request $request, string $companySlug)
    {
        $company = $request->attributes->get('company');
        $branch = Branch::where('company_id', $company->id)->findOrFail($request->query('branch_id'));

        $orders = $this->matchService->openPurchaseOrders($branch->id)->map(function (PurchaseOrder $po) {
            return [
                'id' => $po->id,
                'po_number' => $po->po_number,
                'supplier' => $po->supplier->name,
            ];
        });

        return response()->json($orders);
    }

    public function purchaseOrderLines(Request $request, string $companySlug)
    {
        $company = $request->attributes->get('company');
        $po = PurchaseOrder::with(['items.product', 'items.uom', 'supplier'])
            ->where('status', 'approved')
            ->whereHas('branch', function ($query) use ($company) {
                $query->where('company_id', $company->id);
            })
            ->findOrFail($request->query('purchase_order_id'));

        $summary = $this->matchService->purchaseOrderSummary($po);
        $items = $po->items
            ->filter(fn ($item) => ($summary[$item->id]['open'] ?? 0) > 0)
            ->map(function ($item) use ($summary) {
                return [
                    'purchase_order_item_id' => $item->id,
                    'product' => $item->product->name,
                    'uom' => $item->uom->name,
                    'ordered' => $summary[$item->id]['ordered'],
                    'received' => $summary[$item->id]['received'],
                    'billed' => $summary[$item->id]['billed'],
                    'open' => $summary[$item->id]['open'],
                    'po_unit_price' => round((float) $item->unit_price, 4),
                ];
            })
            ->values();

        return response()->json([
            'purchase_order' => [
                'id' => $po->id,
                'po_number' => $po->po_number,
                'supplier' => $po->supplier->name,
            ],
            'items' => $items,
        ]);
    }

    public function accept(Request $request, string $companySlug, string $id)
    {
        $company = $request->attributes->get('company');
        $invoice = $this->findInvoice($company->id, $id);

        $this->matchService->accept($invoice, $request->input('variance_reasons', []), auth()->id());

        return redirect()->back()->with('success', 'Supplier invoice accepted for payment.');
    }

    public function void(Request $request, string $companySlug, string $id)
    {
        $company = $request->attributes->get('company');
        $invoice = $this->findInvoice($company->id, $id);

        $this->matchService->void($invoice, auth()->id());

        return redirect()->back()->with('success', 'Supplier invoice voided.');
    }

    private function findInvoice(int $companyId, string $id): SupplierInvoice
    {
        return SupplierInvoice::with([
            'branch',
            'supplier',
            'purchaseOrder',
            'createdBy',
            'acceptedBy',
            'voidedBy',
            'items.product',
            'items.uom',
            'items.varianceApprovedBy',
            'items.allocations.purchaseDeliveryItem.purchaseDelivery',
        ])->whereHas('branch', function ($query) use ($companyId) {
            $query->where('company_id', $companyId);
        })->findOrFail($id);
    }
}
