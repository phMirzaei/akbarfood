<?php

namespace App\DTOs;

final readonly class ListOrder
{
    public function __construct(
        public int $actorId,
    ) {}
}
