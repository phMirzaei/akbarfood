<?php

namespace App\DTOs;

final readonly class PromoteToOperator
{
    public function __construct(
        public int $userId,
        public int $actorId,
    ) {}

}
