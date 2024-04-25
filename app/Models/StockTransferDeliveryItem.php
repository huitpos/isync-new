<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockTransferDeliveryItem extends Model
{
    use HasFactory;

    protected $guarded = [];

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
