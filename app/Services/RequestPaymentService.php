<?php

namespace App\Services;

use App\DTOs\RequestPayment;
use App\Exceptions\AuthorizationException;
use App\Exceptions\OrderAlreadyPaidException;
use App\Models\Payment\Payment;

class RequestPaymentService
{
    public function execute(RequestPayment $requestPayment)
    {
        if ($requestPayment->userId !== $requestPayment->order->user_id) {
            throw new AuthorizationException;
        }
        if ($requestPayment->order->status !== 'pending') {
            throw new OrderAlreadyPaidException;
        }

        return Payment::create([
            'order_id' => $requestPayment->order->id,
            'amount' => $requestPayment->order->total_price,
            'status' => 'pending',
        ]);
    }
}
