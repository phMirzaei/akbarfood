<?php

namespace App\Providers;

use App\Services\NotificationService;
use App\Services\TelegramNotificationService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(NotificationService::class, TelegramNotificationService::class);
    }

    public function boot(): void
    {
        //
    }
}
