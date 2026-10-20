<?php

namespace App\Services\Gl;

use App\Models\DebitMemo;
use App\Models\JournalEntry;
use App\Models\PurchaseDelivery;
use App\Models\SupplierInvoice;
use Carbon\Carbon;

class DocumentJournalService
{
    public function __construct(
        private AccountCatalog $catalog,
        private JournalPoster $poster
    ) {
    }

    public function postPurchaseDelivery(PurchaseDelivery $delivery): ?JournalEntry
    {
        $delivery->loadMissing(['items', 'branch']);
        $companyId = (int) $delivery->branch->company_id;
        if (!$this->catalog->enabled($companyId)) {
            return null;
        }

        $amount = 0.0;
        foreach ($delivery->items as $item) {
            $amount += round((float) $item->qty * (float) $item->unit_price, 4);
        }
        $amount = round($amount, 4);
        if ($amount <= 0) {
            return null;
        }

        return $this->poster->post($companyId, [
            'entry_date' => Carbon::today()->toDateString(),
            'source_type' => 'purchase_delivery',
            'source_key' => (string) $delivery->id,
            'description' => 'Purchase delivery '.$delivery->pd_number,
        ], [
            [
                'account_id' => $this->catalog->id($companyId, 'inventory'),
                'branch_id' => $delivery->branch_id,
                'debit' => $amount,
                'credit' => 0,
                'memo' => $delivery->pd_number,
            ],
            [
                'account_id' => $this->catalog->id($companyId, 'unbilled_receipts'),
                'branch_id' => $delivery->branch_id,
                'debit' => 0,
                'credit' => $amount,
                'memo' => $delivery->pd_number,
            ],
        ]);
    }

    public function postSupplierInvoice(SupplierInvoice $invoice): ?JournalEntry
    {
        $invoice->loadMissing(['branch', 'items.allocations.purchaseDeliveryItem']);
        $companyId = (int) $invoice->branch->company_id;
        if (!$this->catalog->enabled($companyId)) {
            return null;
        }

        $deliveryCost = 0.0;
        foreach ($invoice->items as $item) {
            foreach ($item->allocations as $allocation) {
                $unitPrice = (float) ($allocation->purchaseDeliveryItem->unit_price ?? 0);
                $deliveryCost += round((float) $allocation->qty * $unitPrice, 4);
            }
        }
        $deliveryCost = round($deliveryCost, 4);
        $total = round((float) $invoice->total_amount, 4);
        $variance = round($total - $deliveryCost, 4);
        $branchId = $invoice->branch_id;
        $lines = [];

        if (abs($deliveryCost) >= 0.0001) {
            $lines[] = [
                'account_id' => $this->catalog->id($companyId, 'unbilled_receipts'),
                'branch_id' => $branchId,
                'debit' => $deliveryCost,
                'credit' => 0,
                'memo' => 'Matched delivery cost',
            ];
        }
        if ($variance > 0) {
            $lines[] = [
                'account_id' => $this->catalog->id($companyId, 'purchase_price_variance'),
                'branch_id' => $branchId,
                'debit' => $variance,
                'credit' => 0,
                'memo' => 'Price variance',
            ];
        } elseif ($variance < 0) {
            $lines[] = [
                'account_id' => $this->catalog->id($companyId, 'purchase_price_variance'),
                'branch_id' => $branchId,
                'debit' => 0,
                'credit' => abs($variance),
                'memo' => 'Price variance',
            ];
        }
        if (abs($total) >= 0.0001) {
            $lines[] = [
                'account_id' => $this->catalog->id($companyId, 'accounts_payable'),
                'branch_id' => $branchId,
                'debit' => 0,
                'credit' => $total,
                'memo' => $invoice->invoice_number,
            ];
        }

        if (!$lines) {
            return null;
        }

        return $this->poster->post($companyId, [
            'entry_date' => $invoice->invoice_date->toDateString(),
            'source_type' => 'supplier_invoice',
            'source_key' => (string) $invoice->id,
            'description' => 'Supplier invoice '.$invoice->invoice_number,
        ], $lines);
    }

    public function postDebitMemo(DebitMemo $memo): ?JournalEntry
    {
        $memo->loadMissing('branch');
        $companyId = (int) $memo->branch->company_id;
        if (!$this->catalog->enabled($companyId)) {
            return null;
        }

        $total = round((float) $memo->total_amount, 4);
        if ($total <= 0) {
            return null;
        }

        return $this->poster->post($companyId, [
            'entry_date' => $memo->memo_date->toDateString(),
            'source_type' => 'debit_memo',
            'source_key' => (string) $memo->id,
            'description' => 'Debit memo '.$memo->memo_number,
        ], [
            [
                'account_id' => $this->catalog->id($companyId, 'accounts_payable'),
                'branch_id' => $memo->branch_id,
                'debit' => $total,
                'credit' => 0,
                'memo' => $memo->memo_number,
            ],
            [
                'account_id' => $this->catalog->id($companyId, 'inventory'),
                'branch_id' => $memo->branch_id,
                'debit' => 0,
                'credit' => $total,
                'memo' => $memo->memo_number,
            ],
        ]);
    }

    public function voidIfPosted(int $companyId, string $sourceType, string $sourceKey): void
    {
        $entry = JournalEntry::where('company_id', $companyId)
            ->where('source_type', $sourceType)
            ->where('source_key', $sourceKey)
            ->where('status', 'posted')
            ->first();

        if ($entry) {
            $this->poster->void($entry);
        }
    }
}
