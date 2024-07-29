<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TakeOrderDiscountOtherInformation extends Model
{
    protected $table = 'take_order_discount_other_informations';

    protected $connection = 'transactional_db';

    use HasFactory;

    protected $guarded = [];
}
