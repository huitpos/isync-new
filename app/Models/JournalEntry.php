<?php

namespace App\Models;

use App\Traits\CreatedUpdatedBy;
use Illuminate\Database\Eloquent\Model;

class JournalEntry extends Model
{
    use CreatedUpdatedBy;

    protected $guarded = [];

    protected $casts = [
        'entry_date' => 'date',
        'posted_at' => 'datetime',
        'voided_at' => 'datetime',
    ];

    public function lines()
    {
        return $this->hasMany(JournalLine::class)->orderBy('line_no');
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function reversal()
    {
        return $this->belongsTo(JournalEntry::class, 'reversal_entry_id');
    }

    public function postedBy()
    {
        return $this->belongsTo(User::class, 'posted_by');
    }

    public function voidedBy()
    {
        return $this->belongsTo(User::class, 'voided_by');
    }
}
