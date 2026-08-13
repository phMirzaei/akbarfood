<?php

namespace App\Services;

use App\DTOs\RequestPayment;
use App\Exceptions\OrderAlreadyPaidException;
use App\Exceptions\UnauthorizedOrderActionException;
use App\Models\Order\Order;
use App\Models\Payment\Payment;

class RequestPaymentService
{
    public function execute(RequestPayment $requestPayment)
    {
        $order = Order::findOrFail($requestPayment->orderId);
        if ($requestPayment->userId !== $order->user_id) {
            throw new UnauthorizedOrderActionException;
        }
        if (! $order->isPending()) {
            throw new OrderAlreadyPaidException;
        }
        if ($order->payment()->exists()) {
            throw new OrderAlreadyPaidException;
        }

        return Payment::create([
            'order_id' => $order->id,
            'amount' => $order->total_price,
            'status' => 'pending',
        ]);
    }
}
