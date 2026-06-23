<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;


class OtpNotFoundException extends Exception
{
    public function render(): JsonResponse
    {
        return response()->json([
        'message' => 'کد وارد شده صحیح نیست.',
    ], 422);
    }

}
