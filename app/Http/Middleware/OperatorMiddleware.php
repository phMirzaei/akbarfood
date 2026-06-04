<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;


class OperatorMiddleware
{
    public function handle(Request $request, Closure $next): JsonResponse
    {
        $restaurant = $request->route('restaurant');

        $isOperator = $restaurant->users()
            ->where('user_id', auth()->id())
            ->wherePivot('role', 'operator')
            ->exists();

        if (! $isOperator) {
            return response()->json([
                'message' => 'شما اجازه دسترسی ندارید'
            ], 403);
        }
        return $next($request);
    }
}
