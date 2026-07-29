<?php

namespace App\Services;

use App\DTOs\UpdateCartItem;
use App\Models\Cart\Cart;
use App\Models\Cart\CartItem;
use Illuminate\Support\ItemNotFoundException;

class UpdateCartItemService
{
    public function execute(UpdateCartItem $updateCartItem,CartItem $cartItem)
    {
        $cart=Cart::where('user_id',auth()->id())->firstOrFail();
        if($cartItem->cart_id !== $cart->id){
            throw new ItemNotFoundException();
        }
        $cartItem->update([
            'quantity'=>$updateCartItem->quantity,
        ]);
}
}
