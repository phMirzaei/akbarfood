<?php

namespace App\DTOs;

final readonly class RegisterRestaurant
{
    public function __construct(
        public int $ownerId,
        public string $name,
        public string $permitScan,
        public string $permitScanName,
        public string $landline_number,
        public string $city,
        public string $street,
        public string $alley,
        public string $vendor_type
    ) {}
}
