<?php

namespace App\Http\Controllers;

use App\Http\Requests\RegisterUserRequest;
use App\Http\Requests\verifyOTPRequest;
use App\Models\OTP;
use App\Models\User;
use http\Env\Response;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Hash;


class AuthController extends Controller
{
    public function register(RegisterUserRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $code = random_int(1000, 9999);
        $hashedCode = Hash::make($code);
        $phone = $validated['phone'];

        OTP::updateOrCreate(
            ['phone' => $phone],
            [
                'name' => $validated['name'],
                'code' => $hashedCode,
                'expired_at' => now()->addMinutes(10),
            ]
        );


        Http::
        post("https://telegram.craftsmanshipbuff.com:8443/bot" . env('TELEGRAM_BOT_TOKEN') . "/sendMessage", [
            'chat_id' => '-1003740180374',
            'text' => "$code   کد تاییدیه شما به شماره ی : $phone"
        ]);

        return response()->json([
            'message' => 'کد تایید ارسال شد',
        ], 200);
    }

    public function verifyOTP(verifyOTPRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $otp = OTP::where('phone', $validated['phone'])->first();

        if (!Hash::check($validated['code'], $otp->code)) {
            return response()->json([
                'message' => 'کد وارد شده صحیح نیست.',
            ]);
        }

        $otp->delete();
        $user = User::firstOrCreate(
            ['phone' => $otp->phone],
            ['name' => $otp->name]
        );
        $token = JWTAuth::fromUser($user);
        return response()->json([
            'message' => 'ثبت نام شما با موفقیت انجام شد.',
            'token' => $token,
        ]);
    }
}
