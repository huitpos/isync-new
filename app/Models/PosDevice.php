<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PosDevice extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function machine()
    {
        return $this->belongsTo(PosMachine::class, 'pos_machine_id');
    }
}
