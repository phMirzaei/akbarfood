<?php

namespace App\Http\Middleware;

use Closure;

class OperatorMiddleware
{
    public function handle($request, Closure $next)
    {

        $user = auth()->user();

        if ($user) {
            $user->refresh();
        }

        if ($user && ($user->role === 'operator' || $user->role === 'admin')) {
            return $next($request);
        }

        return response()->json([
            'message' => 'دسترسی غیرمجاز.',
        ], 403);
    }
}
