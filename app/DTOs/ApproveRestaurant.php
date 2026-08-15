<?php

namespace App\DTOs;

final readonly class ApproveRestaurant
{
    public function __construct(
        public int $restaurantId,
        public int $actorId,
    ) {}
}
