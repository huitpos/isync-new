<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Traits\CreatedUpdatedBy;

class StockTransferOrder extends Model
{
    use HasFactory;
    use CreatedUpdatedBy;

    protected $guarded = [];

    //items
    public function items()
    {
        return $this->hasMany(StockTransferOrderItem::class);
    }

    //created by
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    //branch
    public function branch()
    {
        return $this->belongsTo(Branch::class, 'destination_branch_id');
    }

    public function sourceBranch()
    {
        return $this->belongsTo(Branch::class, 'source_branch_id');
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    //deliveryLocation
    public function deliveryLocation()
    {
        return $this->belongsTo(DeliveryLocation::class);
    }

}
