<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentTypeAccount extends Model
{
    protected $guarded = [];

    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    public function paymentType()
    {
        return $this->belongsTo(PaymentType::class);
    }
}
