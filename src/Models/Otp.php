<?php

namespace Horlerdipo\SimpleOtp\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property string $destination
 * @property string $destination_type
 * @property string $purpose
 * @property string $token
 * @property Carbon $expires_at
 * @property bool $is_used
 * @property bool $is_hashed
 */
class Otp extends Model
{
    use HasFactory;

    protected $fillable = [
        'destination', 'destination_type',
        'purpose', 'token', 'expires_at',
        'is_used', 'is_hashed',
    ];

    protected $casts = [
        'is_used' => 'boolean',
        'is_hashed' => 'boolean',
    ];
}
