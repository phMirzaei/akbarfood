<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Operator\OperatorController;
use App\Http\Controllers\Operator\RestaurantApprovalController;
use App\Http\Controllers\Restaurants\RestaurantController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function () {
    Route::post('request-otp', [AuthController::class, 'requestOtp']);
    Route::post('verify-otp',  [AuthController::class, 'verifyOtp']);
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

    });

});


