<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryMovementLog extends Model
{
    protected $table = 'inventory_movement_logs';
    protected $connection = 'inventory';

    protected $fillable = [
        'branch_id',
        'product_id',
        'movement_type',
        'object_id',
        'previous_qty',
        'new_qty',
        'processed_by',
        'processed_at',
        'reverted_at',
    ];

    protected $casts = [
        'previous_qty' => 'decimal:2',
        'new_qty' => 'decimal:2',
        'processed_at' => 'datetime',
        'reverted_at' => 'datetime',
    ];

    /**
     * Get the branch associated with this log (from mysql connection)
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    /**
     * Get the product associated with this log (from mysql connection)
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    /**
     * Get the user who processed this movement (from mysql connection)
     */
    public function processedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }
}
