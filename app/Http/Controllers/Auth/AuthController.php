<?php

namespace App\Http\Controllers\Auth;

use App\DTOs\RequestPhoneNumberVerification;
use App\DTOs\VerifyPhoneNumber;
use App\Http\Controllers\Controller;
use App\Http\Requests\SendOtpRequest;
use App\Http\Requests\VerifyOtpRequest;
use App\Services\RequestPhoneNumberVerificationService;
use App\Services\VerifyPhoneNumberService;
use Illuminate\Http\JsonResponse;

class AuthController extends Controller
{
    public function requestOtp(SendOtpRequest $request, RequestPhoneNumberVerificationService $requestPhoneNumberVerificationService): JsonResponse
    {
        $requestPhoneNumberVerificationService->execute(
            new RequestPhoneNumberVerification($request->validated('phone'), $request->validated('name'))
        );

        return response()->json([
            'message' => 'کد تایید ارسال شد',
        ], 200);
    }

    public function verifyOtp(VerifyOtpRequest $request, VerifyPhoneNumberService $verifyPhoneNumberService): JsonResponse
    {
        $phone = $request->validated('phone');
        $code = $request->validated('code');
        $user = $verifyPhoneNumberService->execute(
            new VerifyPhoneNumber($phone, $code)
        );
        $token = auth()->login($user);

        return response()->json([
            'message' => 'ثبت نام شما با موفقیت انجام شد.',
            'name' => $user->name,
            'phone' => $phone,
            'token' => $token,
        ]);

    }
}
