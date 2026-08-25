<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Cart\CartController;
use App\Http\Controllers\Cart\CartItemController;
use App\Http\Controllers\Menu\MenuController;
use App\Http\Controllers\Operator\OperatorController;
use App\Http\Controllers\Operator\RestaurantApprovalController;
use App\Http\Controllers\Order\OrderController;
use App\Http\Controllers\Payment\PaymentController;
use App\Http\Controllers\Restaurants\RestaurantController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {

    Route::prefix('auth')->middleware('throttle:6,1')->group(function () {
        Route::post('request-otp', [AuthController::class, 'requestOtp']);
        Route::post('verify-otp', [AuthController::class, 'verifyOtp']);
    });

    Route::middleware('auth:api')->group(function () {

        Route::post('restaurant-register', [RestaurantController::class, 'register']);

        Route::middleware('admin')->group(function () {
            Route::post('users/{userId}/promote-operator', [OperatorController::class, 'promoteOperator']);
        });

        Route::middleware('operator')->prefix('restaurants')->group(function () {
            Route::get('pending', [RestaurantApprovalController::class, 'getApprovalPendingRegister']);
            Route::patch('{restaurant}/approve', [RestaurantApprovalController::class, 'approveRestaurant']);
            Route::patch('{restaurant}/reject', [RestaurantApprovalController::class, 'rejectRestaurant']);
            Route::get('{restaurant}/permit', [RestaurantController::class, 'getPermit']);
        });

        Route::middleware('restaurantOwner')->prefix('restaurants')->group(function () {
            Route::post('{restaurant}/menu-items', [MenuController::class, 'addMenuItems']);
            Route::put('{restaurant}/menu-items/{menuItem}', [MenuController::class, 'updateMenuItems']);
            Route::delete('{restaurant}/menu-items/{menuItem}', [MenuController::class, 'removeMenuItems']);
        });

        Route::post('restaurants/{restaurant}/cart-items', [CartItemController::class, 'addItemToCart']);

        Route::get('carts', [CartController::class, 'listCartItems']);
        Route::put('cart-items/{cartItem}', [CartItemController::class, 'updateCartItem']);
        Route::delete('cart-items/{cartItem}', [CartItemController::class, 'removeItemFromCart']);

        Route::post('orders', [OrderController::class, 'createOrder']);
        Route::get('orders', [OrderController::class, 'listOrder']);
        Route::patch('orders/{order}/cancel', [OrderController::class, 'cancelOrder']);

        Route::post('orders/{order}/payments', [PaymentController::class, 'sendRequestPayment']);
        Route::post('payments/{payment}/verify', [PaymentController::class, 'verifyPayment']);
    });

    Route::get('restaurants/{restaurant}/menu', [MenuController::class, 'listMenuItems']);
});
