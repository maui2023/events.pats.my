<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Organization extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'status',
        'created_by',
        'approved_at',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
    ];

    public function users()
    {
        return $this->belongsToMany(User::class)->withPivot('role', 'status');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
