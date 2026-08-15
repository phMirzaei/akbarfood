<?php

namespace App\DTOs;

use Illuminate\Http\UploadedFile;

final readonly class AddMenuItem
{
    public function __construct(
        public int $restaurantId,
        public string $name,
        public string $description,
        public string $category,
        public ?string $imagePath,
        public bool $is_available,
        public int $price,
    ) {}
}
