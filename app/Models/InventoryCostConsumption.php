<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InventoryCostConsumption extends Model
{
    protected $guarded = [];

    protected $casts = [
        'qty' => 'float',
        'unit_cost' => 'float',
        'business_date' => 'date',
    ];

    public function layer()
    {
        return $this->belongsTo(InventoryCostLayer::class, 'inventory_cost_layer_id');
    }
}
