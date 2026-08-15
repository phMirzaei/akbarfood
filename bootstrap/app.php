<?php

use App\Exceptions\CartIsEmptyException;
use App\Exceptions\CartItemNotFoundException;
use App\Exceptions\MenuItemNotAvailableException;
use App\Exceptions\MenuItemNotInRestaurantException;
use App\Exceptions\OrderAlreadyCancelledException;
use App\Exceptions\OrderAlreadyPaidException;
use App\Exceptions\OtpBlockedException;
use App\Exceptions\OtpExpiredException;
use App\Exceptions\OtpNotFoundException;
use App\Exceptions\OtpTooManyAttemptsException;
use App\Exceptions\OtpTooManyRequestException;
use App\Exceptions\PaymentFailedException;
use App\Exceptions\RestaurantNotApprovedException;
use App\Exceptions\UnauthorizedException;
use App\Exceptions\UnauthorizedOrderActionException;
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
        $exceptions->render(
            fn (UnauthorizedOrderActionException $e) => response()->json([
                'message' => 'این سفارش متعلق به شما نیست.',
            ], 403)
        );
        $exceptions->render(
            fn (CartIsEmptyException $e) => response()->json([
                'message' => 'سبد خرید شما خالی است.',
            ], 422)
        );
        $exceptions->render(
            fn (CartItemNotFoundException $e) => response()->json([
                'message' => 'آیتم یافت نشد.',
            ], 404)
        );
        $exceptions->render(
            fn (MenuItemNotAvailableException $e) => response()->json([
                'message' => 'آیتم در حال حاضر در دسترس نیست.',
            ], 422)
        );
        $exceptions->render(
            fn (MenuItemNotInRestaurantException $e) => response()->json([
                'message' => 'این آیتم منو متعلق به این رستوران نیست.',
            ], 404)
        );
        $exceptions->render(
            fn (OrderAlreadyCancelledException $e) => response()->json([
                'message' => 'سفارش شما قبلاً لغو شده است.',
            ], 409)
        );
        $exceptions->render(
            fn (OrderAlreadyPaidException $e) => response()->json([
                'message' => 'سفارش شما قبلا پرداخت شده است.',
            ], 409)
        );
        $exceptions->render(
            fn (OtpBlockedException $e) => response()->json([
                'message' => 'به دلیل تلاش‌های ناموفق، تا ۱۲ ساعت مسدود هستید.',
            ], 403)
        );
        $exceptions->render(
            fn (OtpExpiredException $e) => response()->json([
                'message' => 'کد تایید منقضی شده است. لطفاً کد جدید درخواست دهید.',
            ], 410)
        );
        $exceptions->render(
            fn (OtpNotFoundException $e) => response()->json([
                'message' => 'کد وارد شده صحیح نیست.',
            ], 422)
        );
        $exceptions->render(
            fn (OtpTooManyAttemptsException $e) => response()->json([
                'message' => 'تعداد دفعات مجاز به پایان رسید. به مدت 12 ساعت بلاک شدید.',
            ], 429)
        );
        $exceptions->render(
            fn (OtpTooManyRequestException $e) => response()->json([
                'message' => 'لطفاً 1 دقیقه صبر کنید و دوباره تلاش کنید.',
            ], 429)
        );
        $exceptions->render(
            fn (PaymentFailedException $e) => response()->json([
                'message' => 'پرداخت با شکست مواجه شد.',
            ], 422)
        );
        $exceptions->render(
            fn (RestaurantNotApprovedException $e) => response()->json([
                'message' => 'رستوران هنوز تایید نشده است.',
            ], 403)
        );
        $exceptions->render(
            fn (UnauthorizedException $e) => response()->json([
                'message' => 'دسترسی غیر مجاز',
            ], 401)
        );
    })->create();
