<?php

namespace App\DTOs;

readonly final class UpdateCartItem
{
    public function __construct(
        public int $quantity,
    ){}
}
