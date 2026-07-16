<?php

namespace App\Services;

use App\DTOs\RequestPhoneNumberVerification;
use App\Exceptions\OtpBlockedException;
use App\Exceptions\OtpTooManyRequestException;
use App\Exceptions\PhoneAlreadyRegisteredException;
use App\Models\Otp;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class RequestPhoneNumberVerificationService
{
    public function __construct(
        private NotificationService $notificationService,
    )
    {
    }

    public function execute(RequestPhoneNumberVerification $requestPhoneNumberVerification): void
    {
        if (User::where('phone', $requestPhoneNumberVerification->phone)->exists()) {
            throw new PhoneAlreadyRegisteredException();
        }

        $otp = Otp::where('phone', $requestPhoneNumberVerification->phone)->first();
        if ($otp instanceof Otp) {
            if ($otp->next_allowed_request_otp->isFuture()) {
                throw new OtpTooManyRequestException();
            }
            if ($otp->blocked_until && $otp->blocked_until->isFuture()) {
                throw new OtpBlockedException();
            }
        }
        $code = random_int(1000, 9999);
        Otp::updateOrCreate(
            ['phone' => $requestPhoneNumberVerification->phone],
            [
                'code' => Hash::make($code),
                'next_allowed_request_otp' => now()->addMinute(),
                'attempts' => 0,
                'expired_at' => now()->addMinutes(10),
                'payload' => [
                    'name' => $requestPhoneNumberVerification->name
                ]
            ]
        );
        $this->notificationService->send($requestPhoneNumberVerification->phone, "کد تایید {$code}میباشد.");

    }
}
