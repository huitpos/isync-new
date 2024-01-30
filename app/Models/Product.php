<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;

use App\Traits\CreatedUpdatedBy;

class Product extends Model
{
    use HasFactory;
    use CreatedUpdatedBy;

    protected $guarded = [];

    public function bundledItems(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'bundle_product', 'product_id', 'included_product_id')->withPivot('quantity')->as('bundled_item');
    }

    public function rawItems(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_raw', 'product_id', 'raw_product_id')->withPivot(['quantity', 'uom_id'])->as('raw_item');
    }

    public function itemType()
    {
        return $this->belongsTo(ItemType::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function uom()
    {
        return $this->belongsTo(UnitOfMeasurement::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function subcategory()
    {
        return $this->belongsTo(Subcategory::class);
    }
}
