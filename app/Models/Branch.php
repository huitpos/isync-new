<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Traits\CreatedUpdatedBy;

class Branch extends Model
{
    use HasFactory;
    use CreatedUpdatedBy;

    protected $connection = 'mysql';

    protected $guarded = [];

    public function cluster()
    {
        return $this->belongsTo(Cluster::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function region()
    {
        return $this->belongsTo(Region::class);
    }

    public function province()
    {
        return $this->belongsTo(Province::class);
    }

    public function city()
    {
        return $this->belongsTo(City::class);
    }

    public function barangay()
    {
        return $this->belongsTo(Barangay::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function machines()
    {
        return $this->hasMany(PosMachine::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function deliveryLocations()
    {
        return $this->hasMany(DeliveryLocation::class);
    }

    public function products()
    {
        return $this->belongsToMany(Product::class)->withPivot('price', 'stock');
    }

    public function chargeAccounts()
    {
        return $this->hasMany(ChargeAccount::class);
    }
}