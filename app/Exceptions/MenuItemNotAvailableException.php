<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;

class MenuItemNotAvailableException extends Exception
{
    public function render(): JsonResponse
    {
        return response()->json([
            'message' => 'آیتم در حال حاضر در دسترس نیست.',
        ], 422);
    }
}
