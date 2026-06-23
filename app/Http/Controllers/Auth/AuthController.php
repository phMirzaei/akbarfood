<?php

namespace App\Http\Controllers\Auth;

use App\Exceptions\OtpBlockedException;
use App\Exceptions\OtpExpiredException;
use App\Exceptions\OtpNotFoundException;
use App\Exceptions\OtpTooManyAttemptsException;
use App\Http\Controllers\Controller;
use App\Http\Requests\SendOtpRequest;
use App\Http\Requests\VerifyOtpRequest;
use App\Models\User;
use App\Services\OtpService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class AuthController extends Controller
{
    public function requestOtp(SendOtpRequest $request, OtpService $otpService): JsonResponse
    {
        $phone = $request->validated('phone');
            $payload = [
                'name' => $request->validated('name'),
            ];
            $otpService->send($phone, $payload);

            return response()->json([
                'message' => 'کد تایید ارسال شد',
            ], 200);
    }

    public function verifyOtp(VerifyOtpRequest $request, OtpService $otpService): JsonResponse
    {
        $phone = $request->validated('phone');
        $code = $request->validated('code');
        $token = $otpService->verifyAndLogin($phone, $code);

                 return response()->json([
                'message' => 'ثبت نام شما با موفقیت انجام شد.',
                'token' => $token,
            ]);

    }
}
