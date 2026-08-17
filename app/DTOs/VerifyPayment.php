<?php

namespace App\DTOs;

final readonly class VerifyPayment
{
    public function __construct(
        public int $actorId,
        public int $paymentId
    ) {}
}
