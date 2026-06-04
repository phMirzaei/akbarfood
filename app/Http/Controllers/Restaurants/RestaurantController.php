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
                'role' => 'manager'
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

    public function addOperator(Request $request, $restaurantId)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id'
        ]);
        try {
            $restaurant = Restaurant::findOrFail($restaurantId);

             $isManager=$restaurant->users()
                ->where('user_id', auth()->id())
                ->wherePivot('role', 'manager')
                ->exists();
            if (!$isManager) {
                return response()->json([
                    'message' => 'شما اجازه انجام این عملیات را ندارید.'
                ], 403);
            }
            $restaurant->users()->syncWithoutDetaching([
                $request->user_id => ['role' => 'operator']
            ]);

            return response()->json([
                'message' => 'اوبراتور با موفقیت اضافه شد.'
            ]);
        } catch (\Throwable $e) {
            report($e);
            return response()->json([
                'message' => 'خطای سرور.'
            ], 500);

        }


    }

}
