<?php

namespace App\Contracts;

use App\Models\Payment\Payment;

interface PaymentGateway
{
    public function verify(Payment $payment): string;
}
