<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Permission\Models\Role as SpatieRole;

use App\Traits\CreatedUpdatedBy;

class Role extends SpatieRole
{
    use HasFactory;
    use CreatedUpdatedBy;

    protected $fillable = [
        'name',
        'guard_name',
        'company_id',
        'created_by',
        'updated_by',
        'status'
    ];
    
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
