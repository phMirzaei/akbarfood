<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;

class ModelNotFoundException extends Exception
{
    public function render():JsonResponse
    {
       return response()->json([
           'message' => 'این آیتم منو متعلق به این رستوران نیست.'
       ],404);
    }
}
