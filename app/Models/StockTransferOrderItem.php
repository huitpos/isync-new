<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockTransferOrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'stock_transfer_request_id',
        'product_id',
        'uom_id',
        'quantity',
        'str_remarks',
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
