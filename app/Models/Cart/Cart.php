<?php

namespace App\Models\Cart;

use App\Models\Order\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Cart extends Model
{
    public $fillable = ['user_id', 'updated_at', 'created_at'];

    public function items(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);

    }

    public function order(): HasOne
    {
        return $this->hasOne(Order::class);
    }

    public function total(): int
    {
        $this->loadMissing('items.menu');

        return $this->items->sum(
            fn ($item) => $item->menu->price * $item->quantity
        );
    }
}
