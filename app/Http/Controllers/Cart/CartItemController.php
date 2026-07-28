<?php

namespace App\Http\Controllers\Cart;

use App\DTOs\AddItemToCart;
use App\Http\Controllers\Controller;
use App\Http\Requests\Cart\AddItemToCartRequest;
use App\Services\AddItemToCartService;
use Illuminate\Http\JsonResponse;

class CartItemController extends Controller
{
    public function addItemToCart(AddItemToCartRequest $request, AddItemToCartService $addItemToCartService): JsonResponse
    {

        $cartItem = new AddItemToCart(
            menu_id: $request->validated('menu_id'),
            quantity: $request->validated('quantity'),
        );
        $addItemToCartService->execute($cartItem);

        return response()->json([
            'message' => 'آیتم به سبد خرید شما اضافه شد.',
        ]);
    }
}
