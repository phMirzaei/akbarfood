<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\Restaurant\AuthController as AuthRestaurant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
Route::prefix('auth')->group(function () {
    Route::post('request-otp', [AuthController::class, 'requestOtp']);
    Route::post('verify-otp',  [AuthController::class, 'verifyOtp']);
    Route::prefix('restaurant')->group(function () {
        Route::post('register-restaurant', [AuthRestaurant::class, 'registerRestaurant']);
    });
});


