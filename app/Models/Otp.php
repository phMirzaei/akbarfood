<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Otp extends Model
{
    protected $fillable = ['code', 'phone', 'attempts', 'expired_at', 'blocked_until', 'payload', 'next_allowed_request_otp'];

    protected $hidden = ['code'];

    protected function casts(): array
    {
        return [
            'expired_at' => 'datetime',
            'blocked_until' => 'datetime',
            'payload' => 'array',
            'next_allowed_request_otp' => 'datetime',
        ];
    }
}
