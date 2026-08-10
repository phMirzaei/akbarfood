<?php

namespace App\DTOs;

final readonly class DownloadRestaurantPermit
{
    public function __construct(
        public int $restaurantId,
    ) {}
}
