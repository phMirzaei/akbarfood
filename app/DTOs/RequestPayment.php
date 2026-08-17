<?php

namespace App\DTOs;

final readonly class RequestPayment
{
    public function __construct(
        public int $actorId,
        public int $orderId,
    ) {}
}
