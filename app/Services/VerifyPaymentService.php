<?php

namespace App\Services;

use App\DTOs\VerifyPayment;
use App\Exceptions\AuthorizationException;
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
        if ($verifyPayment->payment->status == 'failed') {
            throw new PaymentFailedException;
        }
        if ($verifyPayment->payment->status == 'paid') {
            throw new OrderAlreadyPaidException;
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
