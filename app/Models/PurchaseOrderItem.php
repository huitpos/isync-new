<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseOrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_request_id',
        'product_id',
        'uom_id',
        'unit_price',
        'quantity',
        'total',
        'pr_remarks',
        'balance'
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
}
