<?php

namespace App\DTOs;

final readonly class VerifyPhoneNumber
{
    public function __construct(
        public string $phone,
        public string $code,
    ) {}
}
