<?php

namespace App\Models;

use App\Traits\CreatedUpdatedBy;
use Illuminate\Database\Eloquent\Model;

class FiscalPeriod extends Model
{
    use CreatedUpdatedBy;

    protected $guarded = [];

    protected $casts = [
        'starts_on' => 'date',
        'ends_on' => 'date',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}
