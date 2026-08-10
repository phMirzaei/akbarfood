<?php

namespace App\DTOs;

use App\Models\Restaurant\Restaurant;

readonly final class DownloadRestaurantPermit
{
    public function __construct(
        public Restaurant $restaurant,
    )
    {

}
}
