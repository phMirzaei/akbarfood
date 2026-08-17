<?php

namespace Tests\Feature;

use App\Contracts\PaymentGateway;
use App\Exceptions\PaymentFailedException;
use App\Models\Cart\Cart;
use App\Models\Order\Order;
use App\Models\Payment\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VerifyPaymentTest extends TestCase
{
    use RefreshDatabase;

    public function test_payment_verification_marks_order_as_paid(): void
    {
        $gateway = $this->mock(PaymentGateway::class);
        $gateway->shouldReceive('verify')->once()->andReturn('123456');
        $user = User::create([
            'name' => 'Test',
            'phone' => '0140026305',
            'role' => 'user',
        ]);
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
            'amount' => $order->total_price,
            'status' => 'pending',
        ]);
        $this->actingAs($user);
        $response = $this->postJson("/api/{$payment->id}/verify_payment");
        $response->assertStatus(200);
        $this->assertDatabaseHas('payments', [
            'id' => $payment->id,
            'status' => 'paid',
            'transaction_id' => '123456',
        ]);

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'paid',
        ]);
    }

    public function test_payment_verification_does_not_mark_order_as_paid_when_gateway_fails(): void
    {
        $gateway = $this->mock(PaymentGateway::class);
        $gateway->shouldReceive('verify')->once()->andThrow(PaymentFailedException::class);
        $user = User::create([
            'name' => 'Test',
            'phone' => '0140026305',
            'role' => 'user',
        ]);
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
            'amount' => $order->total_price,
            'status' => 'pending',
        ]);
        $this->actingAs($user);
        $response = $this->postJson("/api/{$payment->id}/verify_payment");
        $response->assertStatus(422);
        $this->assertDatabaseHas('payments', [
            'id' => $payment->id,
            'status' => 'pending',
            'transaction_id' => null,
        ]);
        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'pending',
        ]);
    }
}
