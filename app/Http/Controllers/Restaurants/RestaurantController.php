<?php

namespace App\Http\Controllers\Restaurants;

use App\DTOs\RegisterRestaurantDto;
use App\Http\Controllers\Controller;
use App\Http\Requests\Restaurant\RegisterRequest;
use App\Services\RegisterRestaurantService;
use Illuminate\Http\JsonResponse;
class RestaurantController extends Controller
{
    public function store(RegisterRequest $request, RegisterRestaurantService $service): JsonResponse
    {
        $dto=new RegisterRestaurantDto(
            name: $request->validated('name'),
            permit_scan: $request->validated('permit_scan'),
            landline_number: $request->validated('landline_number'),
            city: $request->validated('city'),
            street: $request->validated('street'),
            alley: $request->validated('alley'),
        );
        $service->execute(
            $dto,
            auth()->id()
        );
        return response()->json([
            'message' => 'درخواست شما برای ثبت رستوران در حال بررسی است.',
        ], 201);
    }


}
