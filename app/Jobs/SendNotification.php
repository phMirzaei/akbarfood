<?php

namespace App\Jobs;

use App\Contracts\Notifier;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SendNotification implements ShouldQueue
{
    use Queueable;

    public int $tries = 5;

    public array $backoff = [30, 60, 120, 300];

    public function __construct(private string $phone, private string $message) {}

    public function handle(Notifier $notifier): void
    {
        $notifier->send($this->phone, $this->message);
    }
}
