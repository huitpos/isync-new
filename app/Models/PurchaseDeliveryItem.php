<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseDeliveryItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_delivery_id',
        'purchase_order_item_id',
        'product_id',
        'uom_id',
        'qty',
        'unit_price',
        'po_unit_price',
    ];

    //product
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    //uom
    public function uom()
    {
        return $this->belongsTo(UnitOfMeasurement::class, 'uom_id');
    }

    //purchase order item
    public function purchaseOrderItem()
    {
        return $this->belongsTo(PurchaseOrderItem::class);
    }
}
