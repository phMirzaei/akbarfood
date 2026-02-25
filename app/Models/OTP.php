<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OTP extends Model
{
    protected $table='otps';
    protected $fillable=['code','phone','name','expired_at'];
}
