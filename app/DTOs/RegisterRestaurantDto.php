<?php

namespace App\DTOs;

use Illuminate\Http\UploadedFile;
class RegisterRestaurantDto
{
    public function __construct(
        public readonly string $name,
        public readonly UploadedFile $permit_scan,
        public readonly string $landline_number,
        public readonly string $city,
        public readonly string $street,
        public readonly string $alley,
    )
    {

}
}
