<?php

namespace App\Jobs;

use App\Contracts\Notifier;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SendNotification implements ShouldQueue
{
    use Queueable;

    public function __construct(private string $phone, private string $message) {}

    public function handle(Notifier $notifier): void
    {
        $notifier->send($this->phone, $this->message);
    }
}
