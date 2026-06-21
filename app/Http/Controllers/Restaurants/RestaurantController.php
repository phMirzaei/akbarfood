<?php

namespace App\Http\Controllers\Restaurants;

use App\Http\Controllers\Controller;
use App\Http\Requests\Restaurant\RegisterRequest;
use App\Services\RegisterRestaurantService;
use Illuminate\Http\JsonResponse;
class RestaurantController extends Controller
{
    public function store(RegisterRequest $request, RegisterRestaurantService $service): JsonResponse
    {
        $service->execute(
            $request->validated(),
            auth()->id()
        );
        return response()->json([
            'message' => 'درخواست شما برای ثبت رستوران در حال بررسی است.',
        ], 201);
    }


}
