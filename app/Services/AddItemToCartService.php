<?php

namespace App\Services;

use App\DTOs\AddItemToCart;
use App\Models\Cart\Cart;

class AddItemToCartService
{
    public function execute(AddItemToCart $addItemToCart)
    {
        $cart = Cart::firstOrCreate([
            'user_id' => auth()->id(),
        ]);
        $item = $cart->items()->where('menu_id', $addItemToCart->menu_id)->first();
        if ($item) {
            $item->quantity = $item->quantity + $addItemToCart->quantity;
            $item->save();
        } else {
            $cart->Items()->create([
                'menu_id' => $addItemToCart->menu_id,
                'quantity' => $addItemToCart->quantity,
                'price' => $addItemToCart->price,
            ]);
        }
    }
}
