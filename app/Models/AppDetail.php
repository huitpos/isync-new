<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use App\Traits\CreatedUpdatedBy;

class AppDetail extends Model
{
    protected $connection = 'mysql';

    use CreatedUpdatedBy;

    protected $guarded = [];

    protected $casts = [
        'version_code' => 'integer',
        'mandatory' => 'boolean',
    ];
}
