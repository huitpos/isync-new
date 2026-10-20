<?php

namespace App\Support;

use App\Models\PurchaseDelivery;
use App\Models\PurchaseOrder;
use Illuminate\Validation\ValidationException;

class PurchaseOrderStatus
{
    public static function apply(PurchaseOrder $po, string $status, int $userId): void
    {
        if (!in_array($status, ['approved', 'rejected'], true)) {
            throw ValidationException::withMessages([
                'status' => 'Choose approve or reject.',
            ]);
        }

        if ($po->status !== 'pending') {
            throw ValidationException::withMessages([
                'status' => 'Only a pending purchase order can be approved or rejected.',
            ]);
        }

        if (PurchaseDelivery::where('purchase_order_id', $po->id)->exists()) {
            throw ValidationException::withMessages([
                'status' => 'This purchase order already has a delivery, so its status can no longer be changed here.',
            ]);
        }

        $po->status = $status;
        $po->action_by = $userId;
        $po->save();
    }
}
