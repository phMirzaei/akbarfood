<?php

namespace App\Services;

use App\DTOs\VerifyPayment;
use App\Exceptions\OrderAlreadyCancelledException;
use App\Exceptions\OrderAlreadyPaidException;
use App\Exceptions\PaymentFailedException;
use App\Exceptions\UnauthorizedOrderActionException;
use Illuminate\Support\Facades\DB;

class VerifyPaymentService
{
    public function execute(VerifyPayment $verifyPayment)
    {
        if ($verifyPayment->userId !== $verifyPayment->payment->order->user_id) {
            throw new UnauthorizedOrderActionException;
        }
        if (! $verifyPayment->payment->order->canBePaid()) {
            throw new OrderAlreadyPaidException;
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
            $verifyPayment->payment->markAsPaid(
                (string) random_int(50000, 100000)
            );
            $verifyPayment->payment->save();

            $verifyPayment->payment->order->markAsPaid();
            $verifyPayment->payment->order->save();
        });

        return $verifyPayment->payment->fresh();

    }
}
