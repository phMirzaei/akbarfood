<?php

namespace App\DTOs;

readonly final class ApproveRestaurant
{
    public function __construct(
        public int $restaurantId,
    )
    {

}
}
