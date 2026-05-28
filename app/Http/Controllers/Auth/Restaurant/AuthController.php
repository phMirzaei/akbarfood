<?php

namespace App\Http\Controllers\Auth\Restaurant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Restaurant\RegisterRequest;
use App\Models\Restaurant\Restaurant;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;


class AuthController extends Controller
{
    public function registerRestaurant(RegisterRequest $request): JsonResponse
    {
        $validated=$request->validated();

        $path=$validated['permit_scan']->store('permits','public');
        $validated['permit_scan']=$path;

        Restaurant::create($validated);

        return response()->json([
            'message'=>'ثبت نام با موفقیت انجام شد.',
        ],201);
    }
}
