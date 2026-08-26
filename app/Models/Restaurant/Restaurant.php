<?php

namespace App\Models\Restaurant;

use App\Enums\RestaurantStatus;
use App\Enums\UserRole;
use App\Enums\VendorType;
use App\Models\Cart\Cart;
use App\Models\Menu\Menu;
use App\Models\Order\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Restaurant extends Model
{
    protected $fillable = ['name', 'permit_scan', 'landline_number', 'city', 'street', 'alley', 'status', 'created_at', 'updated_at', 'vendor_type'];

    protected function casts(): array
    {
        return [
            'status' => RestaurantStatus::class,
            'vendor_type' => VendorType::class,
        ];
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'restaurant_users')
            ->withPivot('role');
    }

    public function menuItems(): HasMany
    {
        return $this->hasMany(Menu::class, 'restaurant_id');
    }

    public function carts(): HasMany
    {
        return $this->hasMany(Cart::class, 'restaurant_id');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'restaurant_id');
    }

    public function owner(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'restaurant_users')
            ->withPivot('role')
            ->wherePivot('role', UserRole::Owner->value);
    }

    public function isPending(): bool
    {
        return $this->status === RestaurantStatus::Pending;
    }

    public function isApproved(): bool
    {
        return $this->status === RestaurantStatus::Approved;
    }

    public function approve(): void
    {
        $this->status = RestaurantStatus::Approved;
    }
}
