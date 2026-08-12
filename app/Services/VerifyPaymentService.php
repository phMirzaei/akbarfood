<?php

namespace App\Services;

use App\DTOs\VerifyPayment;
use App\Exceptions\AuthorizationException;
use App\Exceptions\OrderAlreadyCancelledException;
use App\Exceptions\OrderAlreadyPaidException;
use App\Exceptions\PaymentFailedException;
use Illuminate\Support\Facades\DB;

class VerifyPaymentService
{
    public function execute(VerifyPayment $verifyPayment)
    {
        if ($verifyPayment->userId !== $verifyPayment->payment->order->user_id) {
            throw new AuthorizationException;
        }
        if ($verifyPayment->payment->isFailed()) {
            throw new PaymentFailedException;
        }
        if ($verifyPayment->payment->isPaid()) {
            throw new OrderAlreadyPaidException;
        }
        if ($verifyPayment->payment->order->isCancelled()) {
            throw new OrderAlreadyCancelledException;
        }
        DB::transaction(function () use ($verifyPayment) {
            $verifyPayment->payment->update([
                'status' => 'paid',
                'transaction_id' => random_int(50000, 100000),
                'paid_at' => now(),
            ]);

            $verifyPayment->payment->order()->update([
                'status' => 'paid',
            ]);
        });

        return $verifyPayment->payment->fresh();

    }
}
