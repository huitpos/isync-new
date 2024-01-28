<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Product extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function bundledItems(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'bundle_product', 'product_id', 'included_product_id')->withPivot('quantity')->as('bundled_item');
    }

    public function rawItems(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_raw', 'product_id', 'raw_product_id')->withPivot('quantity')->as('raw_item');
    }

    public function itemType()
    {
        return $this->belongsTo(ItemType::class);
    }
}
