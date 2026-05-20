<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\SendOtpRequest;
use App\Http\Requests\VerifyOTPRequest;
use App\Models\Otp;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;


class AuthController extends Controller
{
    public function requestOtp (SendOtpRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $code = random_int(1000, 9999);
        $hashedCode = Hash::make($code);
        $phone = $validated['phone'];

        $userExists = User::where('phone', $phone)->exists();

        if ($userExists) {
            return response()->json([
                'message' => 'این شماره قبلا ثبت شده است.'
            ], 409);
        }
        Otp::updateOrCreate(
            ['phone' => $phone],
            [
                'name' => $validated['name'],
                'code' => $hashedCode,
            ]
        );


        try {
            Http::post("https://api.telegram.org/bot" . config('services.telegram.bot_token') . "/sendMessage", [
                'chat_id' => '-1003740180374',
                'text' => "$code   کد تاییدیه شما به شماره ی : $phone"
            ])->throw();
            return response()->json([
                'message' => 'کد تایید ارسال شد',
            ], 200);
        }
        catch (\Throwable $e){
            report($e);
            return response()->json([
                'message' => 'ارسال کد با مشکل مواجه شد. لطفا دوباره تلاش کنید.',
            ], 502);
        }



    }

    public function verifyOTP(VerifyOTPRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $otp = Otp::where('phone', $validated['phone'])->first();

        if (!$otp||!Hash::check($validated['code'], $otp->code)) {
            return response()->json([
                'message' => 'کد وارد شده صحیح نیست.',
            ],401);
        }
        $user=DB::transaction(function () use ($otp) {
            $user = User::create([
                'phone' => $otp->phone,
                'name' => $otp->name
            ]);
            $otp->delete();
            return $user;
        });

        $token = JWTAuth::fromUser($user);

        return response()->json([
            'message' => 'ثبت نام شما با موفقیت انجام شد.',
            'user'=> $user->name,
            'token' => $token,
        ]);
    }
}
