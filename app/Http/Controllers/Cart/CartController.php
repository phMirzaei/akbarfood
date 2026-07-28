<?php

namespace App\Http\Controllers\Cart;

use App\Http\Controllers\Controller;
use App\Services\ListCartItemService;
use Illuminate\Http\JsonResponse;

class CartController extends Controller
{
    public function listCartItems(ListCartItemService $listCartItemService): JsonResponse
    {
        return response()->json(
            $listCartItemService->execute(auth()->id()),
        );

    }
}
