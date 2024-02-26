<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Traits\CreatedUpdatedBy;

class UnitOfMeasurement extends Model
{
    use HasFactory;
    use CreatedUpdatedBy;

    protected $connection = 'mysql';

    protected $guarded = [];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function conversions()
    {
        return $this->hasMany(UnitConversion::class, 'from_unit_id');
    }

    public function conversionsTo()
    {
        return $this->hasMany(UnitConversion::class, 'to_unit_id');
    }
}
