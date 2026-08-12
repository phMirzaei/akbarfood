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

class VerifyPhoneNumberService
{
    private function createUser(Otp $otp): User
    {
        $payload = $otp->payload ?? [];

        return User::firstOrCreate(
            ['phone' => $otp->phone],
            ['name' => $payload['name'] ?? 'کاربر']
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

        if ($otp->blocked_until?->isFuture()) {
            throw new OtpBlockedException;
        }

        if (! $otp->matches($verifyPhoneNumber->code)) {
            if ($otp->shouldLimitAttempts()) {
                $otp->block();
                $otp->save();
                throw new OtpTooManyAttemptsException;
            }

            $otp->increment('attempts');

            throw new \DomainException('کد وارد شده اشتباه است.');
        }

        return DB::transaction(function () use ($otp): User {
            $user = $this->createUser($otp);

            $otp->delete();

            return $user;
        });
    }
}
