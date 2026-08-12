<?php

namespace App\Services;

use App\DTOs\RequestPayment;
use App\Exceptions\OrderAlreadyPaidException;
use App\Exceptions\UnauthorizedOrderActionException;
use App\Models\Payment\Payment;

class RequestPaymentService
{
    public function execute(RequestPayment $requestPayment)
    {
        if ($requestPayment->userId !== $requestPayment->order->user_id) {
            throw new UnauthorizedOrderActionException;
        }
        if (! $requestPayment->order->isPending()) {
            throw new OrderAlreadyPaidException;
        }
        if ($requestPayment->order->payment()->exists()) {
            throw new OrderAlreadyPaidException;
        }

        return Payment::create([
            'order_id' => $requestPayment->order->id,
            'amount' => $requestPayment->order->total_price,
            'status' => 'pending',
        ]);
    }
}
