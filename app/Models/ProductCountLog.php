<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductCountLog extends Model
{
    use HasFactory;

    protected $guarded = [];

    //product
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

}