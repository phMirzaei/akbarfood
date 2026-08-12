<?php

namespace App\Models\Menu;

use App\Enums\MenuCategory;
use App\Models\Restaurant\Restaurant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Menu extends Model
{
    protected $fillable = ['name', 'description', 'category', 'image', 'is_available', 'price', 'restaurant_id', 'created_at', 'updated_at'];

    protected function casts(): array
    {
        return [
            'status' => MenuCategory::class,
        ];
    }

    public function restaurant(): BelongsTo
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function isAvailable(): bool
    {
        return $this->is_available;
    }
}
