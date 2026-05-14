<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\SendOtpRequest;
use App\Http\Requests\VerifyOTPRequest;
use App\Models\OTP;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Carbon;


class AuthController extends Controller
{
    public function register(SendOtpRequest $request): JsonResponse
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
        post("https://api.telegram.org/bot" . config('services.telegram.bot_token') . "/sendMessage", [
            'chat_id' => '-1003740180374',
            'text' => "$code   کد تاییدیه شما به شماره ی : $phone"
        ]);

        return response()->json([
            'message' => 'کد تایید ارسال شد',
        ], 200);
    }

    public function verifyOTP(VerifyOTPRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $otp = OTP::where('phone', $validated['phone'])->first();

        if (!Hash::check($validated['code'], $otp->code)) {
            return response()->json([
                'message' => 'کد وارد شده صحیح نیست.',
            ]);
        }

        $user = User::create([
            'phone' => $otp->phone,
            'name' => $otp->name
        ]);
        $otp->delete();

        $token = JWTAuth::fromUser($user);
        return response()->json([
            'message' => 'ثبت نام شما با موفقیت انجام شد.',
            'user'=> $user->name,
            'token' => $token,
        ]);
    }
}
