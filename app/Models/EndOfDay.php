<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EndOfDay extends Model
{
    protected $connection = 'transactional_db';

    use HasFactory;

    protected $guarded = [];

    public function payments()
    {
        return $this->hasMany(EndOfDayPayment::class, 'end_of_day_id', 'end_of_day_id')->where(function ($query) {
            $query->where('branch_id', $this->branch_id);
        });
    }

    public function discounts()
    {
        return $this->hasMany(EndOfDayDiscount::class, 'end_of_day_id', 'end_of_day_id')->where(function ($query) {
            $query->where('branch_id', $this->branch_id);
        });
    }

    public function departments()
    {
        return $this->hasMany(EndOfDayDepartment::class, 'end_of_day_id', 'end_of_day_id')->where(function ($query) {
            $query->where('branch_id', $this->branch_id);
        });
    }

    public function machine()
    {
        return $this->belongsTo(PosMachine::class, 'pos_machine_id');
    }
}
