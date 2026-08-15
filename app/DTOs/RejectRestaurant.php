<?php

namespace App\DTOs;

final readonly class RejectRestaurant
{
    public function __construct(
        public int $restaurantId,
        public int $actorId,
    ) {}
}
