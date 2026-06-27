<?php

namespace App\DTOs;

class RegisterRestaurantDto
{
    public function __construct(
        public readonly string $name,
        public readonly string $permit_scan,
        public readonly string $landline_number,
        public readonly string $city,
        public readonly string $street,
        public readonly string $alley,
    )
    {

}
}
