<?php

namespace App\Services;

use App\DTOs\AddItemToCart;
use App\Exceptions\MenuItemNotAvailableException;
use App\Models\Cart\Cart;
use App\Models\Menu\Menu;
use Illuminate\Support\Facades\DB;

class AddItemToCartService
{
    public function execute(AddItemToCart $addItemToCart)
    {
        DB::transaction(function () use ($addItemToCart) {
            $menu = Menu::findOrFail($addItemToCart->menu_id);
            if (! $menu->isAvailable()) {
                throw new MenuItemNotAvailableException;
            }
            $cart = Cart::firstOrCreate([
                'user_id' => $addItemToCart->userId,
                'restaurant_id' => $addItemToCart->restaurantId,
            ]);
            $item = $cart->items()->lockForUpdate()->where('menu_id', $addItemToCart->menu_id)->first();
            if ($item) {
                $item->quantity = $item->quantity + $addItemToCart->quantity;
                $item->save();
            } else {
                $cart->items()->create([
                    'menu_id' => $addItemToCart->menu_id,
                    'quantity' => $addItemToCart->quantity,
                    'price' => $menu->price,
                ]);
            }

        });

    }
}
