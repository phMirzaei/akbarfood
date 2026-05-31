<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\Restaurant\AuthController as AuthRestaurant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\Restaurant\OperatorRestaurantController;
Route::prefix('auth')->group(function () {
    Route::post('request-otp', [AuthController::class, 'requestOtp']);
    Route::post('verify-otp',  [AuthController::class, 'verifyOtp']);
    Route::prefix('restaurant')->group(function () {
        Route::post('register-restaurant', [AuthRestaurant::class, 'sendRegistrationVerification']);
        Route::post('verify-restaurant', [AuthRestaurant::class, 'verifyRestaurantRegistrationOtp']);
        Route::prefix('operator')->group(function () {
            Route::get('pending',[OperatorRestaurantController::class,'pending']);
            Route::patch('approve/{restaurant}',[OperatorRestaurantController::class,'approved']);
            Route::patch('reject/{restaurant}',[OperatorRestaurantController::class,'rejected']);
        });
    });
});


