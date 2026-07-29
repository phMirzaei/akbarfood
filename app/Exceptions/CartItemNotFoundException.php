<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;

class CartItemNotFoundException extends Exception
{
    public function render():JsonResponse
    {
        return response()->json([
            "message" => "آیتم یافت نشد."
        ],404);
    }
}
