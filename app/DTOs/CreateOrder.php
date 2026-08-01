<?php

namespace App\DTOs;

final readonly class CreateOrder
{
    public function __construct(
        public int $userId,
    ) {}
}
