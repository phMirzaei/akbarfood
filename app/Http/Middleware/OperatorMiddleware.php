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

        if ($user) {
            $user->refresh();
        }

        if ($user && ($user->role === 'operator' || $user->role === 'admin')) {
            return $next($request);
        }

        return response()->json([
            'message' => 'دسترسی غیرمجاز. نقش فعلی شما در دیتابیس: ' . ($user->role ?? 'ندارد')
        ], 403);
    }
}
