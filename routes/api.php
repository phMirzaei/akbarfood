<?php

use App\Http\Controllers\Auth\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Restaurants\RestaurantController;
use App\Http\Controllers\Restaurants\RestaurantApprovalController;
Route::prefix('auth')->group(function () {
    Route::post('request-otp', [AuthController::class, 'requestOtp']);
    Route::post('verify-otp',  [AuthController::class, 'verifyOtp']);
});

Route::middleware('auth:api')->prefix('restaurants')->group(function () {

    Route::post('/', [RestaurantController::class, 'store']);
    Route::post('{restaurant}/operators', [RestaurantController::class, 'addOperator']);

    Route::middleware('operator')->group(function () {

        Route::get('pending', [RestaurantApprovalController::class, 'pending']);
        Route::patch('{restaurant}/approve', [RestaurantApprovalController::class, 'approved']);
        Route::patch('{restaurant}/reject', [RestaurantApprovalController::class, 'rejected']);

    });

});


