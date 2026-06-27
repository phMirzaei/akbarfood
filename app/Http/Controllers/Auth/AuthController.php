<?php

namespace App\Http\Controllers\Auth;

use App\DTOs\SendOtpDto;
use App\Http\Controllers\Controller;
use App\Http\Requests\SendOtpRequest;
use App\Http\Requests\VerifyOtpRequest;
use App\Services\OtpService;
use Illuminate\Http\JsonResponse;

class AuthController extends Controller
{
    public function requestOtp(SendOtpRequest $request, OtpService $otpService): JsonResponse
    {
        $dto = new SendOtpDto(
            phone: $request->validated('phone'),
            payload: [
                'name' => $request->validated('name'),
            ]
        );

        $otpService->send($dto);

        return response()->json([
            'message' => 'کد تایید ارسال شد',
        ], 200);
    }

    public function verifyOtp(VerifyOtpRequest $request, OtpService $otpService): JsonResponse
    {
        $phone = $request->validated('phone');
        $code = $request->validated('code');
        $token = $otpService->verifyAndRegister($phone, $code);

        return response()->json([
            'message' => 'ثبت نام شما با موفقیت انجام شد.',
            'token' => $token,
        ]);

    }
}
