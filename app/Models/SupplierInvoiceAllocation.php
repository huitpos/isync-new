<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SupplierInvoiceAllocation extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function invoiceItem()
    {
        return $this->belongsTo(SupplierInvoiceItem::class, 'supplier_invoice_item_id');
    }

    public function purchaseDeliveryItem()
    {
        return $this->belongsTo(PurchaseDeliveryItem::class);
    }
}
