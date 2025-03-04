<?php

namespace App\Models\Sb;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\PosMachine;

class CutOff extends Model
{
    protected $connection = 'sb_db';

    use HasFactory;

    protected $guarded = [];

    public function machine()
    {
        return $this->belongsTo(PosMachine::class, 'pos_machine_id');
    }
}
