<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;


class OperatorMiddleware
{
    public function handle($request, Closure $next)
    {
        $user = auth()->user();
        $restaurant = $request->route('restaurant');

        if (! $restaurant) {

            $isOperator = $user->restaurants()
                ->wherePivotIn('role', ['operator'])
                ->exists();

            if (! $isOperator) {
                return response()->json([
                    'message' => 'شما اجازه دسترسی ندارید'
                ], 403);
            }

            return $next($request);
        }

        $hasAccess = $restaurant->users()
            ->where('user_id', $user->id)
            ->wherePivot('role', 'operator')
            ->exists();

        if (! $hasAccess) {
            return response()->json([
                'message' => 'شما اجازه دسترسی ندارید'
            ], 403);
        }

        return $next($request);
    }
}
