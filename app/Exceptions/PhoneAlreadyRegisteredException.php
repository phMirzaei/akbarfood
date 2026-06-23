<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;


class PhoneAlreadyRegisteredException extends Exception
{
    public function render(): JsonResponse
    {
        return response()->json([
            'message'=>"این شماره قبلا ثبت شده است."
        ]);
    }
}
