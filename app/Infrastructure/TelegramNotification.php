<?php

namespace App\Infrastructure;

use App\Contracts\Notifier;
use Illuminate\Support\Facades\Http;

class TelegramNotification implements Notifier
{
    public function send(string $phone, string $message): void
    {
        Http::post(
            'https://api.telegram.org/bot'.config('services.telegram.bot_token').'/sendMessage',
            [
                'chat_id' => config('services.telegram.chat_id'),
                'text' => sprintf('این پیام به %s ارسال شد.     پیام:%s', $phone, $message), ]
        )->throw();
    }
}
