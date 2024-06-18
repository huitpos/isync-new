<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentOtherInformation extends Model
{
    protected $table = 'payment_other_informations';

    protected $connection = 'transactional_db';

    use HasFactory;

    protected $guarded = [];
}
