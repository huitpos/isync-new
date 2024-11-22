<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Discount extends Model
{
    protected $connection = 'transactional_db';

    use HasFactory;

    protected $guarded = [];

    public function otherInfo()
    {
        return $this->hasMany(DiscountOtherInformation::class, 'discount_id', 'discount_id')->where(function ($query) {
            $query->where('branch_id', $this->branch_id)
                  ->where('pos_machine_id', $this->pos_machine_id)
                  ->where('is_void', 0);
        });
    }

    public function transaction()
    {
        return $this->belongsTo(Transaction::class, 'transaction_id', 'transaction_id')->where(function ($query) {
            $query->where('branch_id', $this->branch_id)
                  ->where('pos_machine_id', $this->pos_machine_id);
        });
    }
}
