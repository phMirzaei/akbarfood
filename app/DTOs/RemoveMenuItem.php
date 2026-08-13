<?php

namespace App\DTOs;

final readonly class RemoveMenuItem
{
    public function __construct(
        public int $menuId,
        public int $restaurantId,
    ) {}
}
