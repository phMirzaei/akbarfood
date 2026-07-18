<?php

namespace App\Http\Controllers\Restaurants;

use App\DTOs\RegisterRestaurant;
use App\Http\Controllers\Controller;
use App\Http\Requests\Restaurant\RegisterRequest;
use App\Services\RegisterRestaurantService;
use Illuminate\Http\JsonResponse;

class RestaurantController extends Controller
{
    public function register(RegisterRequest $request, RegisterRestaurantService $service): JsonResponse
    {
        $path = $request->file('permit_scan')->store('permits', 'public');
        $dto = new RegisterRestaurant(
            name: $request->validated('name'),
            permit_scan: $path,
            landline_number: $request->validated('landline_number'),
            city: $request->validated('city'),
            street: $request->validated('street'),
            alley: $request->validated('alley'),
            vendor_type: $request->validated('vendor_type'),
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
