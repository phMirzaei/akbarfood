<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;

class CartIsEmptyException extends Exception
{
    public function render(): JsonResponse
    {
        return response()->json([
            'message' => 'سبد خرید شما خالی است.',
        ], 422);
    }
}
