<?php

namespace App\Services;

use App\Models\Cart\Cart;

class ListCartItemService
{
    public function execute(int $userId)
    {
        $cart = Cart::firstOrCreate([
            'user_id' => $userId,
        ]);
        $items = $cart->items->map(function ($item) {
            return [
                'name' => $item->menu->name,
                'price' => $item->price,
                'quantity' => $item->quantity,
                'total_price' => $item->price * $item->quantity,
            ];
        });

        return [
            'items' => $items,
            'total' => $items->sum('total_price'),
        ];

    }
}
