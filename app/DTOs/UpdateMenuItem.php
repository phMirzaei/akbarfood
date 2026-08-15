<?php

namespace App\DTOs;

final readonly class UpdateMenuItem
{
    public function __construct(
        public int $actorId,
        public int $restaurantId,
        public int $menuId,
        public string $name,
        public string $description,
        public string $category,
        public ?string $imagePath,
        public bool $is_available,
        public int $price
    ) {}
}
