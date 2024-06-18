<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DiscountOtherInformation extends Model
{
    protected $table = 'discount_other_informations';

    protected $connection = 'transactional_db';

    use HasFactory;

    protected $guarded = [];
}
