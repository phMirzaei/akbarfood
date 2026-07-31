<?php

namespace App\Services;

use App\Exceptions\CartIsEmptyException;
use App\Models\Cart\Cart;
use App\Models\Order\Order;
use Illuminate\Support\Facades\DB;

class CreateOrderService
{
    public function execute()
    {
        return DB::transaction(function () {
            $cart = Cart::with('items.menu')
                ->where('user_id', auth()->id())
                ->firstOrFail();
            if (! $cart || $cart->items->isEmpty()) {
                throw new CartIsEmptyException;
            }
            $order = Order::create([
                'user_id' => auth()->id(),
                'cart_id' => $cart->id,
                'status' => 'pending',
                'total_price' => 200100,
            ]);
            $total_price = 0;
            foreach ($cart->items as $cartItem) {
                $unit_price = $cartItem->menu->price;
                $order->items()->create([
                    'order_id' => $order->id,
                    'menu_id' => $cartItem->menu_id,
                    'unit_price' => $unit_price,
                    'name' => $cartItem->menu->name,
                    'quantity' => $cartItem->quantity,
                ]);
                $total_price += $unit_price * $cartItem->quantity;
            }
            $order->update([
                'total_price' => $total_price,
            ]);

            $cart->items()->delete();

            return $order->load('items');
        });

    }
}
