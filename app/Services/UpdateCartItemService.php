<?php

namespace App\Services;

use App\DTOs\UpdateCartItem;
use App\Exceptions\CartItemNotFoundException;
use App\Models\Cart\Cart;
use App\Models\Cart\CartItem;

class UpdateCartItemService
{
    public function execute(UpdateCartItem $updateCartItem)
    {
        $cart = Cart::where('user_id', $updateCartItem->userId)
            ->firstOrFail();
        $cartItem=CartItem::where('id',$updateCartItem->cartItemId)
            ->where('cart_id',$cart->id)
            ->first();
        if (!$cartItem) {
            throw new CartItemNotFoundException;
        }
        $cartItem->update([
            'quantity' => $updateCartItem->quantity,
        ]);
    }
}
