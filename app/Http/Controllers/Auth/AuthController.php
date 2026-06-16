<?php

namespace App\Http\Controllers\Auth;

use App\Exceptions\OtpBlockedException;
use App\Exceptions\OtpExpiredException;
use App\Exceptions\OtpNotFoundException;
use App\Exceptions\OtpTooManyAttemptsException;
use App\Exceptions\OtpTooManyRequestException;
use App\Http\Controllers\Controller;
use App\Http\Requests\SendOtpRequest;
use App\Http\Requests\VerifyOtpRequest;
use App\Models\User;
use App\Services\OtpService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AuthController extends Controller
{
    public function requestOtp(SendOtpRequest $request, OtpService $otpService): JsonResponse
    {
        $phone = $request->validated('phone');
        try {
            $userExists = User::where('phone', $phone)->exists();

            if ($userExists) {
                return response()->json([
                    'message' => 'این شماره قبلا ثبت شده است.'
                ], 409);
            }
            $payload = [
                'name' => $request->validated('name'),
            ];
            $otpService->send($phone, $payload);

            return response()->json([
                'message' => 'کد تایید ارسال شد',
            ], 200);

        } catch (OtpBlockedException $e) {

            return response()->json([
                'message' => 'به دلیل تلاش‌های ناموفق، تا ۱۲ ساعت مسدود هستید.',
            ], 403);
        } catch (OtpTooManyRequestException $e) {
            return response()->json([
                'message' => 'لطفاً 1 دقیقه صبر کنید و دوباره تلاش کنید.',
            ], 429);
        } catch (\Throwable $e) {

            report($e);

            return response()->json([
                'message' => 'ارسال کد با مشکل مواجه شد. لطفا دوباره تلاش کنید.',
            ], 500);
        }
    }

    public function verifyOtp(VerifyOtpRequest $request, OtpService $otpService): JsonResponse
    {
        $phone = $request->validated('phone');
        $code = $request->validated('code');
        try {
            $otp = $otpService->verify($phone, $code);
            $payload = $otp->payload ?? [];
            $user = DB::transaction(function () use ($otp, $payload) {
                $user = User::firstOrCreate(
                    ['phone' => $otp->phone],
                    [
                        'name' => $payload['name'] ?? 'کاربر',
                    ]
                );
                $otp->delete();
                return $user;
            });
            $token = auth()->login($user);
            return response()->json([
                'message' => 'ثبت نام شما با موفقیت انجام شد.',
                'token' => $token,
            ]);

        } catch (OtpNotFoundException $e) {
            return response()->json([
                'message' => 'کد وارد شده صحیح نیست.',
            ], 422);
        } catch (OtpExpiredException $e) {
            return response()->json([
                'message' => 'کد تایید منقضی شده است. لطفاً کد جدید درخواست دهید.'
            ], 410);
        } catch (OtpBlockedException $e) {
            return response()->json([
                'message' => 'به دلیل تلاش‌های ناموفق، تا ۱۲ ساعت مسدود هستید.'
            ], 403);
        } catch (OtpTooManyAttemptsException $e) {
            return response()->json([
                'message' => 'تعداد دفعات مجاز به پایان رسید. به مدت 12 ساعت بلاک شدید.'
            ], 403);
        } catch (\Throwable $e) {

            report($e);

            return response()->json([
                'message' => 'خطای سرور.'
            ], 500);
        }

    }
}
