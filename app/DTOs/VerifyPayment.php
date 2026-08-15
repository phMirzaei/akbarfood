<?php

namespace App\DTOs;

final readonly class VerifyPayment
{
    public function __construct(
        public int $userId,
        public int $paymentId
    ) {}
}
