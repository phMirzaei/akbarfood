<?php

use App\Http\Controllers\Auth\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Restaurants\RestaurantController;
use App\Http\Controllers\Restaurants\RestaurantApprovalController;
use App\Http\Controllers\OperatorController;
Route::prefix('auth')->group(function () {
    Route::post('request-otp', [AuthController::class, 'requestOtp']);
    Route::post('verify-otp',  [AuthController::class, 'verifyOtp']);
});

Route::middleware('auth:api')->prefix('restaurants')->group(function () {

    Route::post('register', [RestaurantController::class, 'store']);
    Route::middleware('admin')->group(function () {
        Route::post('users/{userId}/add-operator', [OperatorController::class, 'addOperator']);
    });
    Route::middleware('operator')->prefix('restaurants')->group(function () {

        Route::get('pending', [RestaurantApprovalController::class, 'pending']);
        Route::patch('{restaurant}/approve', [RestaurantApprovalController::class, 'approved']);
        Route::patch('{restaurant}/reject', [RestaurantApprovalController::class, 'rejected']);

    });

});


