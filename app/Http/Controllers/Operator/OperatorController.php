<?php

namespace App\Http\Controllers\Operator;

use App\DTOs\PromoteToOperator;
use App\Http\Controllers\Controller;
use App\Services\PromoteToOperatorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OperatorController extends Controller
{
    public function promoteOperator(Request $request, PromoteToOperatorService $promoteToOperatorService, $userId): JsonResponse
    {
        $promoteToOperatorService->execute(
            new PromoteToOperator(
                userId: $userId,
                actorId: auth()->id(),
            )
        );

        return response()->json([
            'message' => 'اوبراتور با موفقیت اضافه شد.',
        ]);

    }
}
