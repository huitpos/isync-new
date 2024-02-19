<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Traits\CreatedUpdatedBy;

class DiscountType extends Model
{
    use HasFactory;
    use CreatedUpdatedBy;

    protected $guarded = [];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function fields()
    {
        return $this->hasMany(DiscountTypeFields::class);
    }
}
