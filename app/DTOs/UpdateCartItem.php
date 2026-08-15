<?php

namespace App\DTOs;

final readonly class UpdateCartItem
{
    public function __construct(
        public int $quantity,
        public int $cartItemId,
        public int $userId,
    ) {}
}
