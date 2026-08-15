<?php

namespace App\Http\Controllers\Cart;

use App\DTOs\ListCartItem;
use App\Http\Controllers\Controller;
use App\Services\ListCartItemService;
use Illuminate\Http\JsonResponse;

class CartController extends Controller
{
    public function listCartItems(ListCartItemService $listCartItemService): JsonResponse
    {
        $listCartItem = new ListCartItem(
            userId: auth()->id()
        );

        return response()->json(
            $listCartItemService->execute($listCartItem),
        );

    }
}
