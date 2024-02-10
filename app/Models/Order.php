<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $connection = 'transactional_db';

    use HasFactory;

    protected $guarded = [];

    public function uom()
    {
        return $this->belongsTo(UnitOfMeasurement::class, 'unit_id');
    }
}
