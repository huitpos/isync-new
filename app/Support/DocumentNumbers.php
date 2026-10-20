<?php

namespace App\Support;

use App\Models\Branch;
use App\Models\PurchaseOrder;
use Illuminate\Support\Facades\DB;

class DocumentNumbers
{
    public static function purchaseOrder(Branch $branch): string
    {
        return DB::transaction(function () use ($branch) {
            $prefix = 'PO' . strtoupper($branch->code) . date('Ymd');
            $last = PurchaseOrder::where('po_number', 'like', $prefix . '%')
                ->lockForUpdate()
                ->orderByDesc('po_number')
                ->value('po_number');

            $next = 1;
            if ($last && preg_match('/(\d{4})$/', $last, $matches)) {
                $next = ((int) $matches[1]) + 1;
            }

            return $prefix . str_pad((string) $next, 4, '0', STR_PAD_LEFT);
        });
    }
}
