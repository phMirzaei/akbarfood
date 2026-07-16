<?php

namespace App\Services;

interface NotificationService
{
    public function send(string $phone, string $message): void;
}
