<?php

namespace App\DTOs;

use Illuminate\Http\UploadedFile;

readonly final class AddMenuItem
{
    public function __construct(
        public string $name,
        public string $description,
        public string $category,
        public UploadedFile $image,
        public bool $is_available,
        public int $price,
    ){}
        }
