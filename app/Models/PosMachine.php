<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PosMachine extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function device()
    {
        return $this->hasOne(PosDevice::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }
}
