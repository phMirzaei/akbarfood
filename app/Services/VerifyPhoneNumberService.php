<?php

namespace App\Services;

use App\DTOs\VerifyPhoneNumber;
use App\Exceptions\OtpBlockedException;
use App\Exceptions\OtpExpiredException;
use App\Exceptions\OtpNotFoundException;
use App\Exceptions\OtpTooManyAttemptsException;
use App\Models\Otp;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class VerifyPhoneNumberService
{
    private function isVerificationCodeWrong(VerifyPhoneNumber $verifyPhoneNumber, Otp $otp): bool
    {
        return ! Hash::check($verifyPhoneNumber->code, $otp->code);
    }

    private function shouldLimitAttempts(Otp $otp): bool
    {
        return ($otp->attempts + 1) >= 3;
    }

    private function limitPhoneNumber(Otp $otp): void
    {
        $otp->update([
            'attempts' => 4,
            'blocked_until' => now()->addHours(12),
        ]);
    }

    private function createUser(Otp $otp): User
    {
        $payload = $otp->payload ?? [];

        return User::firstOrCreate(
            [
                'phone' => $otp->phone,
                'name' => $payload['name'] ?? 'کاربر',
            ]
        );
    }

    public function execute(VerifyPhoneNumber $verifyPhoneNumber): User
    {
        $otp = Otp::where('phone', $verifyPhoneNumber->phone)->first();
        if (! $otp) {
            throw new OtpNotFoundException;
        }
        if ($otp->expired_at->isPast()) {
            $otp->delete();
            throw new OtpExpiredException;
        }
        if ($otp?->blocked_until?->isFuture()) {
            throw new OtpBlockedException;
        }
        if ($this->isVerificationCodeWrong($verifyPhoneNumber, $otp)) {
            if ($this->shouldLimitAttempts($otp)) {
                $this->limitPhoneNumber($otp);
                throw new OtpTooManyAttemptsException;
            }
            $otp->increment('attempts');
            throw new \DomainException('کد وارد شده اشتباه است.');
        }
        $user = DB::transaction(function () use ($otp) {

            $user = $this->createUser($otp);

            $otp->delete();

            return $user;
        });

        return $user;
    }
}
