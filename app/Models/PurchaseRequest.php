<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Traits\CreatedUpdatedBy;

class PurchaseRequest extends Model
{
    use HasFactory;
    use CreatedUpdatedBy;

    protected $guarded = [];

    public function items()
    {
        return $this->hasMany(PurchaseRequestItem::class);
    }

    //branch
    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    //department
    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    //deliveryLocation
    public function deliveryLocation()
    {
        return $this->belongsTo(DeliveryLocation::class);
    }

    //supplier
    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    //barangay
    public function barangay()
    {
        return $this->belongsTo(Barangay::class);
    }

    //city
    public function city()
    {
        return $this->belongsTo(City::class);
    }

    //province
    public function province()
    {
        return $this->belongsTo(Province::class);
    }

    //region
    public function region()
    {
        return $this->belongsTo(Region::class);
    }

    public function actionBy()
    {
        return $this->belongsTo(User::class, 'action_by');
    }
}