<?php

namespace App\Contracts;

interface NotificationService
{
    public function send(string $phone, string $message): void;
}
