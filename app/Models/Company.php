<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Traits\CreatedUpdatedBy;

class Company extends Model
{
    use HasFactory;
    use CreatedUpdatedBy;

    protected $guarded = [];

    public function clusters()
    {
        return $this->hasMany(Cluster::class);
    }

    public function branches()
    {
        return $this->hasMany(Branch::class);
    }

    public function departments()
    {
        return $this->hasMany(Department::class);
    }

    public function suppliers()
    {
        return $this->hasMany(Supplier::class);
    }

    public function categories()
    {
        return $this->hasMany(Category::class);
    }

    public function subcategories()
    {
        return $this->hasMany(Subcategory::class);
    }

    public function unitOfMeasurements()
    {
        return $this->hasMany(UnitOfMeasurement::class);
    }

    public function paymentTypes()
    {
        return $this->hasMany(PaymentType::class);
    }

    public function chargeAccounts()
    {
        return $this->hasMany(ChargeAccount::class);
    }

    public function banks()
    {
        return $this->hasMany(Bank::class);
    }

    public function discountTypes()
    {
        return $this->hasMany(DiscountType::class);
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

    public function itemTypes()
    {
        return $this->hasMany(ItemType::class);
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function visibleProducts()
    {
        return $this->hasMany(Product::class)->whereHas('itemType', function ($query) {
            $query->where('show_in_cashier', true);
        });
    }

    public function rawProducts()
    {
        return $this->hasMany(Product::class)->whereHas('itemType', function ($query) {
            $query->where('show_in_cashier', false);
        });
    }

    public function client()
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
