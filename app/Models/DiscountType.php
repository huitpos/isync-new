<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;

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

    public function departments(): BelongsToMany
    {
        return $this->belongsToMany(Department::class);
    }

    public function fields()
    {
        return $this->hasMany(DiscountTypeFields::class);
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'discount_product')
            ->withPivot('type', 'discount')
            ->withTimestamps();
    }
}
