<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;

class AuthorizationException extends Exception
{
    public function render(): JsonResponse
    {
        return response()->json([
            'message' => 'این سفارش متعلق به شما نیست.',
        ], 403);
    }
}
