<?php

namespace App\Http\Controllers\Restaurants;

use App\Http\Controllers\Controller;
use App\Http\Requests\Restaurant\RegisterRequest;
use App\Models\Restaurant\Restaurant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RestaurantController extends Controller
{
    public function store(RegisterRequest $request): JsonResponse
    {
        $validated = $request->validated();

        try {
            $path = $validated['permit_scan']->store('permits', 'public');
            $validated['permit_scan'] = $path;
            $restaurant = Restaurant::create([
                'name' => $validated['name'],
                'permit_scan' => $path,
                'landline_number' => $validated['landline_number'],
                'city' => $validated['city'],
                'street' => $validated['street'],
                'alley' => $validated['alley'],
                'status' => 'pending',
            ]);
            $restaurant->users()->attach(auth()->id(), [
                'role' => 'owner'
            ]);
            return response()->json([
                'message' => 'درخواست شما برای ثبت رستوران در حال بررسی است.',
            ], 201);
        } catch (\Throwable $e) {

            report($e);

            return response()->json([
                'message' => 'خطای سرور.'
            ], 500);

        }
    }


}
