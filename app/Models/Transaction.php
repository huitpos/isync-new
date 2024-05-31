<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $connection = 'transactional_db';

    use HasFactory;

    protected $guarded = [];

    public function machine()
    {
        return $this->belongsTo(PosMachine::class, 'pos_machine_id');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function items()
    {
        return $this->hasMany(Order::class, 'transaction_id', 'transaction_id')->where(function ($query) {
            $query->where('branch_id', $this->branch_id)
                  ->where('pos_machine_id', $this->pos_machine_id);
        });
    }

    public function payments()
    {
        return $this->hasMany(Payment::class, 'transaction_id', 'transaction_id')->where(function ($query) {
            $query->where('branch_id', $this->branch_id)
                  ->where('pos_machine_id', $this->pos_machine_id);
        });
    }
}
