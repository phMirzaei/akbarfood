<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;

class RestaurantNotApprovedException extends Exception
{
    public function render(): JsonResponse
    {
        return response()->json([
            'message' => 'رستوران هنوز تایید نشده است.',
        ], 403);
    }
}
