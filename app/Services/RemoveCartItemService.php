<?php

namespace App\Services;

use App\DTOs\RemoveCartItem;
use App\Exceptions\CartItemNotFoundException;
use App\Models\Cart\Cart;
use App\Models\Cart\CartItem;

class RemoveCartItemService
{
    public function execute(RemoveCartItem $removeCartItem): void
    {
        $cart = Cart::where('user_id', $removeCartItem->userId)->first();
        if (! $cart) {
            throw new CartItemNotFoundException;
        }
        $cartItem = CartItem::where('id', $removeCartItem->cartItemId)
            ->where('cart_id', $cart->id)
            ->first();
        if (! $cartItem) {
            throw new CartItemNotFoundException;
        }

        $cartItem->delete();
    }
}
