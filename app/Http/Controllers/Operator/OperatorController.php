<?php

namespace App\Http\Controllers\Operator;

use App\DTOs\PromoteToOperator;
use App\Http\Controllers\Controller;
use App\Services\PromoteToOperatorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OperatorController extends Controller
{
    public function promoteOperator(Request $request, promoteToOperatorService $promoteToOperatorService, $userId): JsonResponse
    {
        $promoteToOperatorService->execute(
            new PromoteToOperator(auth()->id(), $userId)
        );

        return response()->json([
            'message' => 'اوبراتور با موفقیت اضافه شد.'
        ]);


    }

}
