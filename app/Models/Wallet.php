<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Wallet extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'wallet_id',
        'wallet_address',
        'credit_balance',
    ];

    protected $casts = [
        'credit_balance' => 'decimal:4',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

