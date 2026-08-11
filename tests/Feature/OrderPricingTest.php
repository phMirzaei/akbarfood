<?php

namespace Tests\Feature;

use App\DTOs\CreateOrder;
use App\DTOs\RequestPayment;
use App\Models\Cart\Cart;
use App\Models\Cart\CartItem;
use App\Models\Menu\Menu;
use App\Models\Restaurant\Restaurant;
use App\Models\User;
use App\Services\CreateOrderService;
use App\Services\RequestPaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderPricingTest extends TestCase
{
    use RefreshDatabase;

    public function test_cart_total_and_charged_amount_are_the_same_after_price_edit(): void
    {
        $user = User::create([
            'name' => 'Admin',
            'phone' => '0140026305',
        ]);

        $restaurant = Restaurant::create([
            'name' => 'Restaurant',
            'permit_scan' => 'jjjj',
            'landline_number' => 5531633,
            'city' => 'ااااا',
            'street' => 'شسشسش',
            'alley' => 'سسسسس',
            'vendor_type' => 'cafe',
            'status' => 'approved',
        ]);

        $menu = Menu::create([
            'name' => 'Menu',
            'description' => 'Menu description',
            'category' => 'Fast_food',
            'image' => 'menu.png',
            'is_available' => true,
            'price' => 100,
            'restaurant_id' => $restaurant->id,
        ]);

        $cart = Cart::create([
            'user_id' => $user->id,
        ]);

        CartItem::create([
            'cart_id' => $cart->id,
            'menu_id' => $menu->id,
            'price' => 100,
            'quantity' => 1,
        ]);

        $menu->update([
            'price' => 150,
        ]);

        $cartTotal = $cart->load('items.menu')->total();

        $this->assertSame(150, $cartTotal);

        $this->actingAs($user, 'api');

        $order = app(CreateOrderService::class)->execute(
            new CreateOrder(
                userId: $user->id
            )
        );

        $this->assertSame(
            150,
            $order->items->first()->unit_price
        );

        $this->assertSame(
            $order->total_price,
            $order->items->sum(
                fn ($item) => $item->unit_price * $item->quantity
            )
        );

        $payment = app(RequestPaymentService::class)->execute(
            new RequestPayment(
                userId: $user->id,
                order: $order
            )
        );

        $this->assertSame(
            $order->total_price,
            $payment->amount
        );
    }
}
