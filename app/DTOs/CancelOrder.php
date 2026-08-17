<?php

namespace App\DTOs;

final readonly class CancelOrder
{
    public function __construct(
        public int $orderId,
        public int $actorId,
    ) {}
}
