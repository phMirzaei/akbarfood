<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;

class PaymentFailedException extends Exception
{
    public function render(): JsonResponse
    {
        return response()->json([
            'message' => 'پرداخت با شکست مواجه شد.',
        ], 422);
    }
}
