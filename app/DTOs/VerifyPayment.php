<?php

namespace App\DTOs;

use App\Models\Payment\Payment;

final readonly class VerifyPayment
{
    public function __construct(
        public int $userId,
        public Payment $payment
    ) {}
}
