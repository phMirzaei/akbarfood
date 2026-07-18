<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;

class OtpTooManyRequestException extends Exception
{
    public function render(): JsonResponse
    {
        return response()->json([
            'message' => 'لطفاً 1 دقیقه صبر کنید و دوباره تلاش کنید.',
        ], 429);
    }
}
