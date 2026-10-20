<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseRequestQuotationItem extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'unit_price' => 'float',
        'available_qty' => 'float',
        'awarded_qty' => 'float',
        'is_awarded' => 'boolean',
    ];

    public function quotation()
    {
        return $this->belongsTo(PurchaseRequestQuotation::class, 'purchase_request_quotation_id');
    }

    public function purchaseRequestItem()
    {
        return $this->belongsTo(PurchaseRequestItem::class);
    }
}
