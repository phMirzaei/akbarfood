<?php

namespace App\DTOs;

class SendOtpDto
{
    public function __construct(

        public readonly string $phone,
        public readonly SendPayloadDto $payload,
    )
    {
    }
}
