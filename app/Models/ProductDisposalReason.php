<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductDisposalReason extends Model
{
    use HasFactory;

    protected $guarded = [];

    //company
    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}
