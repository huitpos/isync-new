<?php

namespace App\Models\Sb;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CutOffDepartment extends Model
{
    protected $connection = 'sb_db';

    use HasFactory;

    protected $guarded = [];
}
