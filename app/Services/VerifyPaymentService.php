<?php

namespace App\Services;

use App\Exceptions\OrderAlreadyPaidException;
use App\Exceptions\PaymentFailedException;
use App\Models\Payment\Payment;

class VerifyPaymentService
{
    public function execute(Payment $payment)
    {
        if ($payment->status == 'failed') {
            throw new PaymentFailedException;
        }
        if ($payment->status == 'paid') {
            throw new OrderAlreadyPaidException;
        }
        $payment->update([
            'status' => 'paid',
            'transaction_id' => random_int(50000, 100000),
            'paid_at' => now(),
        ]);

        $payment->order()->update([
            'status' => 'paid',
        ]);

        return $payment->fresh();

    }
}
