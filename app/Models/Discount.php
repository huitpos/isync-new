<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Discount extends Model
{
    protected $connection = 'transactional_db';

    use HasFactory;

    protected $guarded = [];

    public function otherInfo()
    {
        return $this->hasMany(DiscountOtherInformation::class, 'discount_id', 'discount_id')->where(function ($query) {
            $query->where('branch_id', $this->branch_id)
                  ->where('pos_machine_id', $this->pos_machine_id)
                  ->where('is_void', 0);
        });

        // INNER JOIN discount_other_informations ON discounts.discount_id = discount_other_informations.discount_id AND discounts.branch_id = discount_other_informations.branch_id AND discounts.pos_machine_id = discount_other_informations.pos_machine_id
    }
}
