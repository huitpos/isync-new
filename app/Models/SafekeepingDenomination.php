<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SafekeepingDenomination extends Model
{
    protected $connection = 'transactional_db';

    use HasFactory;

    protected $guarded = [];
}
