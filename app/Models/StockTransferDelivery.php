<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\CreatedUpdatedBy;

class StockTransferDelivery extends Model
{
    use HasFactory;
    protected $guarded = [];

    use CreatedUpdatedBy;

    //items
    public function items()
    {
        return $this->hasMany(StockTransferDeliveryItem::class);
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

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }
}
