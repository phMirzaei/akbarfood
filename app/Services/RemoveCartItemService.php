<?php

namespace App\Services;

use App\Exceptions\CartItemNotFoundException;
use App\Models\Cart\CartItem;
use Illuminate\Support\Facades\Auth;

class RemoveCartItemService
{
    public function execute(CartItem $cartItem)
    {
        $cart = Auth::user()->cart;

        if (! $cart || $cartItem->cart_id !== $cart->id) {
            throw new CartItemNotFoundException;
        }

        $cartItem->delete();
    }
}
