<?php

namespace App\DTOs;

readonly final class RejectRestaurant
{
    public function __construct(
        public int $restaurantId,
    )
    {

}
}
