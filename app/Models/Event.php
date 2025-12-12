<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    use HasFactory;

    protected $fillable = [
        'organizer_id',
        'organization_id',
        'title',
        'slug',
        'description',
        'start_at',
        'end_at',
        'location',
        'postcode',
        'country',
        'banner_path',
        'is_published',
    ];

    protected $casts = [
        'start_at' => 'datetime',
        'end_at' => 'datetime',
        'is_published' => 'boolean',
    ];

    public function organizer()
    {
        return $this->belongsTo(User::class, 'organizer_id');
    }

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }
}
