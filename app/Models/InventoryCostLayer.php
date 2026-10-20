<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InventoryCostLayer extends Model
{
    protected $guarded = [];

    protected $casts = [
        'qty_received' => 'float',
        'qty_remaining' => 'float',
        'unit_cost' => 'float',
        'received_at' => 'datetime',
    ];

    public function consumptions()
    {
        return $this->hasMany(InventoryCostConsumption::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }
}
