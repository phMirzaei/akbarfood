<?php

namespace App\Services;

use App\DTOs\VerifyPayment;
use App\Exceptions\OrderAlreadyCancelledException;
use App\Exceptions\OrderAlreadyPaidException;
use App\Exceptions\PaymentFailedException;
use App\Exceptions\UnauthorizedOrderActionException;
use App\Models\Payment\Payment;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class VerifyPaymentService
{
    public function execute(VerifyPayment $verifyPayment)
    {
        $payment = Payment::findOrFail($verifyPayment->paymentId);
        $actor = User::findOrFail($payment->actorId);
        if (! $actor->ownsOrder($payment->order)) {
            throw new UnauthorizedOrderActionException;
        }

        if ($payment->order->isCancelled()) {
            throw new OrderAlreadyCancelledException;
        }
        if ($payment->isFailed()) {
            throw new PaymentFailedException;
        }
        if ($payment->isPaid()) {
            throw new OrderAlreadyPaidException;
        }

        DB::transaction(function () use ($payment) {
            $payment->markAsPaid(
                (string) random_int(50000, 100000)
            );
            $payment->save();

            $payment->order->markAsPaid();
            $payment->order->save();
        });

        return $payment->fresh();

    }
}
