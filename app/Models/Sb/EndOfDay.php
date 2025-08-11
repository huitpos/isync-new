<?php

namespace App\Models\Sb;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\EndOfDayPayment;
use App\Models\EndOfDayDiscount;
use App\Models\EndOfDayDepartment;
use App\Models\PosMachine;

class EndOfDay extends Model
{
    protected $connection = 'sb_db';

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
