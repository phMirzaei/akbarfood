<?php

namespace App\Services;
use Illuminate\Support\Facades\Http;
class TelegramService
{
    public function sendMessage(string $message): void
    {
        Http::post(
            "https://api.telegram.org/bot" . config('services.telegram.bot_token') . "/sendMessage",
            [
                'chat_id' => config('services.telegram.chat_id'),
                'text' => $message,
            ]
        )->throw();
    }
}
