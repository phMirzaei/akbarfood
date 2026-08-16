<?php

namespace App\Providers;

use App\Contracts\PermitStorage;
use App\Infrastructure\LocalPermitStorage;
use App\Services\NotificationService;
use App\Services\TelegramNotificationService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(NotificationService::class, TelegramNotificationService::class);
        $this->app->bind(PermitStorage::class,LocalPermitStorage::class);
    }

    public function boot(): void
    {
        //
    }
}
