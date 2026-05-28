<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Otp extends Model
{
    protected $fillable=['code','phone','name','attempts','expired_at','blocked_until'];
    protected $hidden=['code'];
    protected function casts():array
    {
        return [
            'expired_at'=>'datetime',
            'blocked_until'=>'datetime'
        ];
    }
}
