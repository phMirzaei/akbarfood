<?php

namespace App\Services;

use App\DTOs\ListCartItem;
use App\Models\Cart\Cart;

class ListCartItemService
{
    public function execute(ListCartItem $listCartItem)
    {
        $cart = Cart::where('user_id', $listCartItem->userId)->first();
        if (! $cart) {
            return [
                'items' => [],
                'total' => 0,
            ];
        }

        $items = $cart->items->map(function ($item) {
            return [
                'name' => $item->menu->name,
                'price' => $item->menu->price,
                'quantity' => $item->quantity,
                'total_price' => $item->menu->price * $item->quantity,
            ];
        });

        return [
            'items' => $items,
            'total' => $cart->total(),
        ];

    }
}
