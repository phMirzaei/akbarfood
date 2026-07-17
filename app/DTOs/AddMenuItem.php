<?php

namespace App\DTOs;

readonly final class AddMenuItem
{
    public function __construct(
        public string $name,
        public string $description,
        public string $category,
        public string $image,
        public bool $is_available,
        public int $price,
    ){}
        }
