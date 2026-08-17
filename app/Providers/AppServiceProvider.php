<?php

namespace App\Providers;

use App\Contracts\Notifier;
use App\Contracts\PaymentGateway;
use App\Contracts\PermitStorage;
use App\Infrastructure\FakePaymentGateway;
use App\Infrastructure\LocalPermitStorage;
use App\Infrastructure\TelegramNotifier;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(Notifier::class, TelegramNotifier::class);
        $this->app->bind(PermitStorage::class, LocalPermitStorage::class);
        $this->app->bind(PaymentGateway::class, FakePaymentGateway::class);
    }

    public function boot(): void
    {
        //
    }
}
