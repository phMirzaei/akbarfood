<?php

namespace App\Infrastructure;

use App\Contracts\PaymentGateway;
use App\Models\Payment\Payment;

class FakePaymentGateway implements PaymentGateway
{
    public function verify(Payment $payment): string
    {
        return (string) random_int(50000, 100000);
    }
}
