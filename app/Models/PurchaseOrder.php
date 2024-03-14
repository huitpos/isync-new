<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Traits\CreatedUpdatedBy;

class PurchaseOrder extends Model
{
    use HasFactory;
    use CreatedUpdatedBy;

    protected $guarded = [];

    //items
    public function items()
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    //branch
    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    //created by
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    //purchaseRequest
    public function purchaseRequest()
    {
        return $this->belongsTo(PurchaseRequest::class);
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

    //paymentTerm
    public function paymentTerm()
    {
        return $this->belongsTo(PaymentTerm::class);
    }

    //supplierTerm
    public function supplierTerm()
    {
        return $this->belongsTo(SupplierTerm::class);
    }
}
