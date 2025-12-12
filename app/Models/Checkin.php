<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Checkin extends Model
{
    protected $fillable = [
        'attendee_id',
        'status',
        'scanned_at',
    ];

    protected $casts = [
        'scanned_at' => 'datetime',
    ];

    public function attendee(): BelongsTo
    {
        return $this->belongsTo(Attendee::class);
    }
}
