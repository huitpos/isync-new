<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Traits\CreatedUpdatedBy;

class PurchaseDelivery extends Model
{
    use HasFactory;
    use CreatedUpdatedBy;

    protected $fillable = [
        'branch_id',
        'purchase_order_id',
        'pd_number',
        'sales_invoice_number',
        'delivery_number',
        'total_qty',
        'total_amount',
        'status',
        'action_by',
        'action_by',
    ];

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    //items
    public function items()
    {
        return $this->hasMany(PurchaseDeliveryItem::class);
    }

    //branch
    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    //purchaseOrder
    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    //actionBy
    public function actionBy()
    {
        return $this->belongsTo(User::class, 'action_by');
    }
}
