<?php

namespace App\Contracts;

interface Notifier
{
    public function send(string $phone, string $message): void;
}
