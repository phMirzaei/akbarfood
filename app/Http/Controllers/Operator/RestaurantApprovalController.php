<?php

namespace App\Http\Controllers\Operator;

use App\DTOs\ApproveRestaurant;
use App\DTOs\RejectRestaurant;
use App\Http\Controllers\Controller;
use App\Models\Restaurant\Restaurant;
use App\Services\ApproveRestaurantService;
use App\Services\RejectRestaurantService;
use Illuminate\Http\JsonResponse;

class RestaurantApprovalController extends Controller
{
    public function getApprovalPendingRegister(): JsonResponse
    {
        return response()->json(
            Restaurant::where('status', 'pending')->get()
        );
    }

    public function approveRestaurant(Restaurant $restaurant, ApproveRestaurantService $approveRestaurantService): JsonResponse
    {
        $approveRestaurantService->execute(
            new ApproveRestaurant(
                restaurantId: $restaurant->id,
                actorId: auth()->id()
            ),
        );

        return response()->json(['message' => 'تایید شد.'], 200);
    }

    public function rejectRestaurant(Restaurant $restaurant, RejectRestaurantService $rejectRestaurantService): JsonResponse
    {
        $rejectRestaurantService->execute(
            new RejectRestaurant(
                restaurantId: $restaurant->id,
                actorId: auth()->id()
            )
        );

        return response()->json(['message' => ' رد شد.'], 200);

    }
}
