<?php

namespace App\DTOs;

final readonly class AddItemToCart
{
    public function __construct(
        public int $menu_id,
        public int $quantity,
        public float $price,
    ) {}
}
