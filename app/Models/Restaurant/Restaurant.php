<?php

namespace App\Models\Restaurant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Restaurant extends Authenticatable implements JWTSubject
{
    use HasFactory;

    protected $fillable = ['name', 'permit_scan', 'landline_number', 'city', 'street', 'alley', 'management_full_name', 'phone','status'];

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims(): array
    {
        return [];
    }
}
