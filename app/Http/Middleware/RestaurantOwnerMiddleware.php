<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RestaurantOwnerMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {

        $user = auth()->user();
        $restaurant = $request->route('restaurant');
        $isOwner = $user->restaurants()->
        where('restaurant_id', $restaurant->id)->
        wherePivot('role', 'owner')->
        exists();

        if (! $isOwner) {
            return response()->json([
                'message' => 'شما اجازه دسترسی ندارید.',
            ], 403);
        }

        return $next($request);
    }
}
