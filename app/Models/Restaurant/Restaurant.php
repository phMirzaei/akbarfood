<?php

namespace App\Models\Restaurant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Models\User;

class Restaurant extends Model{
    use HasFactory;

    protected $fillable = ['name', 'permit_scan', 'landline_number', 'city', 'street', 'alley', 'status','created_at','updated_at'];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'restaurant_users')
            ->withPivot('role');
    }

    public function owner(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'restaurant_users')
            ->withPivot('role')
            ->wherePivot('role', 'owner');
    }
}
