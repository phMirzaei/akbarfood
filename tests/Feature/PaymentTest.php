<?php

namespace Tests\Feature;

use App\Models\Cart\Cart;
use App\Models\Order\Order;
use App\Models\Payment\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_cancelled_order_cannot_be_paid(): void
    {
        $user = User::create([
            'name' => 'Test',
            'phone' => '0140026305',
            'role' => 'user',
        ]);
        $this->actingAs($user, 'api');
        $cart = Cart::create([
            'user_id' => $user->id,
        ]);
        $order = Order::create([
            'user_id' => $user->id,
            'cart_id' => $cart->id,
            'status' => 'pending',
            'total_price' => 10000,
        ]);
        $payment = Payment::create([
            'order_id' => $order->id,
            'amount' => 10000,
            'status' => 'pending',
        ]);

        $this->putJson("/api/{$order->id}/cancel_order")->assertOk();
        $response = $this->postJson("/api/{$payment->id}/verify_payment");
        $response->assertStatus(409);
        $this->assertSame('cancelled', $order->fresh()->status);
    }
}
