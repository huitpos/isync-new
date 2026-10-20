<?php

namespace App\Services;

use App\Models\PurchaseDelivery;
use App\Models\PurchaseRequest;
use App\Models\PurchaseReturn;
use App\Models\SupplierInvoice;
use Illuminate\Support\Facades\DB;

class WorkQueueService
{
    public function counts(int $companyId, ?int $branchId = null): array
    {
        $branchScope = function ($query) use ($companyId, $branchId) {
            $query->where('company_id', $companyId);
            if ($branchId) {
                $query->where('branches.id', $branchId);
            }
        };

        $pendingRequests = PurchaseRequest::query()
            ->whereIn('status', ['pending', 'for_review'])
            ->whereHas('branch', $branchScope)
            ->count();

        $pendingDeliveries = PurchaseDelivery::query()
            ->where('status', 'pending')
            ->whereHas('branch', $branchScope)
            ->count();

        $openInvoices = SupplierInvoice::query()
            ->whereIn('status', ['draft', 'matched', 'variance'])
            ->whereHas('branch', $branchScope)
            ->count();

        $openToBill = PurchaseDelivery::query()
            ->where('status', 'approved')
            ->where(function ($query) {
                $query->where('is_legacy_billed', false)->orWhereNull('is_legacy_billed');
            })
            ->whereHas('branch', $branchScope)
            ->whereHas('items', function ($query) {
                $query->whereRaw('purchase_delivery_items.qty > (
                    SELECT COALESCE(SUM(supplier_invoice_allocations.qty), 0)
                    FROM supplier_invoice_allocations
                    INNER JOIN supplier_invoice_items ON supplier_invoice_items.id = supplier_invoice_allocations.supplier_invoice_item_id
                    INNER JOIN supplier_invoices ON supplier_invoices.id = supplier_invoice_items.supplier_invoice_id
                    WHERE supplier_invoice_allocations.purchase_delivery_item_id = purchase_delivery_items.id
                        AND supplier_invoices.status != \'void\'
                )');
            })
            ->count();

        $returnsWithoutDebit = PurchaseReturn::query()
            ->where('status', 'approved')
            ->whereHas('branch', $branchScope)
            ->whereDoesntHave('items.debitMemoItems.debitMemo', function ($query) {
                $query->where('status', '!=', 'void');
            })
            ->count();

        $lowStock = DB::table('branch_product')
            ->join('products', 'products.id', '=', 'branch_product.product_id')
            ->join('branches', 'branches.id', '=', 'branch_product.branch_id')
            ->where('branches.company_id', $companyId)
            ->when($branchId, function ($query) use ($branchId) {
                $query->where('branch_product.branch_id', $branchId);
            })
            ->where('products.minimum_stock_level', '>', 0)
            ->whereColumn('branch_product.stock', '<=', 'products.minimum_stock_level')
            ->count();

        return [
            'pending_requests' => $pendingRequests,
            'pending_deliveries' => $pendingDeliveries,
            'open_invoices' => $openInvoices,
            'open_to_bill' => $openToBill,
            'returns_without_debit' => $returnsWithoutDebit,
            'low_stock' => $lowStock,
        ];
    }
}
