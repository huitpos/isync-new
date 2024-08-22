<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChangePriceReason extends Model
{
    use HasFactory;

    protected $guarded = [];
    protected $table = 'price_change_reasons';

    //company
    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}
