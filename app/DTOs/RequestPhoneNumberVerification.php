<?php

namespace App\DTOs;

readonly final class RequestPhoneNumberVerification
{
    public function __construct(
        public string $phone,
        public string $name,
    )
    {

}
}
