<?php

namespace App\Models\Restaurant;

use App\Models\Menu\Menu;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Restaurant extends Model
{
    protected $fillable = ['name', 'permit_scan', 'landline_number', 'city', 'street', 'alley', 'status', 'created_at', 'updated_at', 'vendor_type'];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'restaurant_users')
            ->withPivot('role');
    }

    public function menuItems(): HasMany
    {
        return $this->hasMany(Menu::class, 'restaurant_id');
    }

    public function owner(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'restaurant_users')
            ->withPivot('role')
            ->wherePivot('role', 'owner');
    }
}
