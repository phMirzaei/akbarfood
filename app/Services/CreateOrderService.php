<?php

namespace App\Services;

use App\DTOs\CreateOrder;
use App\Exceptions\CartIsEmptyException;
use App\Models\Cart\Cart;
use App\Models\Order\Order;
use Illuminate\Support\Facades\DB;

class CreateOrderService
{
    public function execute(CreateOrder $createOrder)
    {
        return DB::transaction(function () use ($createOrder) {
            $cart = Cart::with('items.menu')
                ->where('user_id', $createOrder->userId)
                ->where('restaurant_id', $createOrder->restaurantId)
                ->firstOrFail();
            if ($cart->items->isEmpty()) {
                throw new CartIsEmptyException;
            }
            $total_price = $cart->total();
            $order = Order::create([
                'user_id' => $createOrder->userId,
                'cart_id' => $cart->id,
                'restaurant_id' => $createOrder->restaurantId,
                'status' => 'pending',
                'total_price' => $total_price,
            ]);
            foreach ($cart->items as $cartItem) {
                $order->items()->create([
                    'order_id' => $order->id,
                    'menu_id' => $cartItem->menu_id,
                    'unit_price' => $cartItem->menu->price,
                    'name' => $cartItem->menu->name,
                    'quantity' => $cartItem->quantity,
                ]);
            }

            $cart->items()->delete();

            return $order;
        });

    }
}
