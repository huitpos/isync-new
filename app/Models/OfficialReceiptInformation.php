<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OfficialReceiptInformation extends Model
{
    //tablename
    protected $table = 'official_receipt_informations';
    protected $connection = 'transactional_db';

    use HasFactory;

    protected $guarded = [];
}
