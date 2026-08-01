<?php

namespace App\Services;

use App\Exceptions\OrderAlreadyPaidException;
use App\Models\Order\Order;
use App\Models\Payment\Payment;

class RequestPaymentService
{
    public function execute(Order $order)
    {
        if ($order->status !== 'pending') {
            throw new OrderAlreadyPaidException;
        }
        Payment::create([
            'order_id' => $order->id,
            'amount' => $order->total_price,
            'status' => $order->status,
        ]);
    }
}
