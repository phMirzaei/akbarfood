<?php

namespace App\DTOs;

final readonly class ListCartItem
{
    public function __construct(
        public int $userId,
    ) {}
}
