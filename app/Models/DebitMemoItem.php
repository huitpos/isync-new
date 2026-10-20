<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DebitMemoItem extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function debitMemo()
    {
        return $this->belongsTo(DebitMemo::class);
    }

    public function supplierInvoiceItem()
    {
        return $this->belongsTo(SupplierInvoiceItem::class);
    }

    public function purchaseReturnItem()
    {
        return $this->belongsTo(PurchaseReturnItem::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function uom()
    {
        return $this->belongsTo(UnitOfMeasurement::class, 'uom_id');
    }
}
