<?php

namespace App\DTOs;

final readonly class RemoveCartItem
{
    public function __construct(
        public int $userId,
        public int $cartItemId
    ) {}

}
