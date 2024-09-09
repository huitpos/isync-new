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

    public function nonVoiditems()
    {
        return $this->hasMany(Order::class, 'transaction_id', 'transaction_id')->where(function ($query) {
            $query->where('branch_id', $this->branch_id)
                  ->where('is_void', false)
                  ->where('pos_machine_id', $this->pos_machine_id);
        });
    }

    public function discounts()
    {
        return $this->hasMany(Discount::class, 'transaction_id', 'transaction_id')->where(function ($query) {
            $query->where('branch_id', $this->branch_id)
                  ->where('pos_machine_id', $this->pos_machine_id);
        });
    }

    public function discountDetails()
    {
        return $this->hasMany(DiscountDetail::class, 'transaction_id', 'transaction_id')->where(function ($query) {
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

    public function paymentOtherInformations()
    {
        return $this->hasMany(PaymentOtherInformation::class, 'transaction_id', 'transaction_id')->where(function ($query) {
            $query->where('branch_id', $this->branch_id)
                  ->where('pos_machine_id', $this->pos_machine_id);
        });
    }

    public function officialReceiptInformations()
    {
        return $this->hasMany(OfficialReceiptInformation::class, 'transaction_id', 'transaction_id')->where(function ($query) {
            $query->where('branch_id', $this->branch_id)
                  ->where('pos_machine_id', $this->pos_machine_id);
        });
    }
}