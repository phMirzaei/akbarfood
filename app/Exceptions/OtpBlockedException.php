<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;

class OtpBlockedException extends Exception
{
    public function render(): JsonResponse
    {
        return response()->json([
            'message' => 'به دلیل تلاش‌های ناموفق، تا ۱۲ ساعت مسدود هستید.',
        ], 403);
    }
}
