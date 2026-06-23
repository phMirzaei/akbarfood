<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;


class OtpTooManyAttemptsException extends Exception
{
    public function render(): JsonResponse
    {
        return response()->json([
            "message" => "تعداد دفعات مجاز به پایان رسید. به مدت 12 ساعت بلاک شدید."
        ],429);
    }
}
