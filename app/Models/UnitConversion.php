<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UnitConversion extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function fromUnit()
    {
        return $this->belongsTo(UnitOfMeasurement::class, 'from_unit_id');
    }

    public function toUnit()
    {
        return $this->belongsTo(UnitOfMeasurement::class, 'to_unit_id');
    }
}
