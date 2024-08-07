<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Traits\CreatedUpdatedBy;

class PosMachine extends Model
{
    protected $connection = 'mysql';

    use HasFactory;
    use CreatedUpdatedBy;

    protected $guarded = [];

    public function device()
    {
        return $this->hasOne(PosDevice::class);
    }

    public function deviceInfo()
    {
        return $this->hasOne(PosDevice::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
