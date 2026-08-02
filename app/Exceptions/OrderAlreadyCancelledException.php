<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;

class OrderAlreadyCancelledException extends Exception
{
    public function render(): JsonResponse
    {
        return response()->json([
            'message' => 'سفارش شما قبلاً لغو شده است.',
        ], 409);
    }
}
