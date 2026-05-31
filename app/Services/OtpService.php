<?php

namespace App\Services;

use App\Exceptions\OtpBlockedException;
use App\Exceptions\OtpExpiredException;
use App\Exceptions\OtpNotFoundException;
use App\Exceptions\OtpTooManyAttemptsException;
use App\Exceptions\OtpTooManyRequestException;
use App\Exceptions\UserAlreadyExistsException;
use App\Models\Otp;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class OtpService
{
    public function send(string $phone,array $payload=[]): void
    {

        $key = "otp_sent:{$phone}";

        if (Cache::has($key)) {
            throw new OtpTooManyRequestException("لطفاً 1 دقیقه صبر کنید و دوباره تلاش کنید.");
        }

        $otp = Otp::where('phone', $phone)->first();

        if ($otp?->blocked_until?->isFuture()) {
            throw new OtpBlockedException("به دلیل تلاش‌های ناموفق، تا ۱۲ ساعت مسدود هستید.");
        }

        $code = random_int(1000, 9999);
        Otp::updateOrCreate(
            ['phone' => $phone],
            [
                'code' => Hash::make($code),
                'attempts' => 0,
                'expired_at' => now()->addMinutes(10),
                'payload' => $payload
            ]
        );

        Http::post(
            "https://api.telegram.org/bot" . config('services.telegram.bot_token') . "/sendMessage",
            [
                'chat_id' => '-1003740180374',
                'text' => "$code کد تاییدیه شما به شماره : $phone"
            ]
        )->throw();

        Cache::put($key, true, now()->addMinute());
    }

    public function verify(string $phone,string $code): Otp
    {
        $otp = Otp::where('phone', $phone)->first();

        if (!$otp) {
            throw new OtpNotFoundException("کد وارد شده صحیح نیست.");
        }
        if ($otp->expired_at?->isPast()) {
            $otp->delete();
            throw new OtpExpiredException("کد تایید منقضی شده است. لطفاً کد جدید درخواست دهید.");
        }
        if ($otp?->blocked_until?->isFuture()) {
            throw new OtpBlockedException("به دلیل تلاش‌های ناموفق، تا ۱۲ ساعت مسدود هستید.");
        }
        if ($otp->attempts >= 3) {
            $otp->update([
                'blocked_until' => now()->addHours(12),
            ]);
            throw new OtpTooManyAttemptsException("تعداد دفعات مجاز به پایان رسید. به مدت 12 ساعت بلاک شدید.");
        }
        if (!Hash::check($code, $otp->code)) {
            $otp->increment('attempts');
            throw new OtpNotFoundException("کد وارد شده صحیح نیست.");
        }
        return $otp;
    }
}
