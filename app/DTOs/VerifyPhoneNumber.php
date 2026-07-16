<?php

namespace App\DTOs;

readonly final class VerifyPhoneNumber
{
    public function __construct(
        public string $phone,
        public string $code,
    )
    {
}
}
