<?php

use App\Http\Middleware\AdminMiddleware;
use App\Http\Middleware\OperatorMiddleware;
use App\Http\Middleware\RestaurantOwnerMiddleware;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'operator' => OperatorMiddleware::class,
            'admin' => AdminMiddleware::class,
            'restaurantOwner' => RestaurantOwnerMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
