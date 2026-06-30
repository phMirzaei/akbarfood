<?php

namespace App\DTOs;

class SendPayloadDto
{
public function __construct(
    public readonly string $name,
){}
}
