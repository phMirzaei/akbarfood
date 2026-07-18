<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;

class OtpExpiredException extends Exception
{
    public function render(): JsonResponse
    {
        return response()->json([
            'message' => 'کد تایید منقضی شده است. لطفاً کد جدید درخواست دهید.',
        ], 410);
    }
}
