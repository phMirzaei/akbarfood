<?php

namespace App\Models\Cart;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cart extends Model
{
    public $fillable = ['user_id', 'updated_at', 'created_at'];

    public function items(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }
}
