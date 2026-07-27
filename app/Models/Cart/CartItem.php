<?php

namespace App\Models\Cart;

use App\Models\Menu\Menu;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CartItem extends Model
{
    public $fillable = ['cart_id', 'menu_id', 'price', 'quantity', 'created_at', 'updated_at'];

    public function cart(): BelongsTo
    {
        return $this->belongsTo(Cart::class);
    }

    public function menu(): BelongsTo
    {
        return $this->belongsTo(Menu::class);
    }
}
