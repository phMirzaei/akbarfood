<?php

namespace App\DTOs;

readonly final class PromoteToOperator
{
    public function __construct(
        public int $actorId,
        public int $userId
    )
    {
    }

}
