<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;

class OrderAlreadyPaidException extends Exception
{
    public function render(): JsonResponse
    {
        return response()->json([
            'message' => 'سفارش شما قبلا پرداخت شده است.',
        ], 409);
    }
}
