<?php

namespace App\Http\Controllers\Cart;

use App\DTOs\AddItemToCart;
use App\DTOs\UpdateCartItem;
use App\Http\Controllers\Controller;
use App\Http\Requests\Cart\AddItemToCartRequest;
use App\Http\Requests\Cart\UpdateCartItemRequest;
use App\Models\Cart\CartItem;
use App\Services\AddItemToCartService;
use App\Services\RemoveCartItemService;
use App\Services\UpdateCartItemService;
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

    public function updateCartItem(UpdateCartItemRequest $request, UpdateCartItemService $updateCartItemService, CartItem $cartItem): JsonResponse
    {
        $UpdatedCartItem = new UpdateCartItem(
            quantity: $request->validated('quantity'),
        );
        $updateCartItemService->execute($UpdatedCartItem, $cartItem);

        return response()->json([
            'message' => 'آیتم ویرایش شد.',
        ]);
    }

    public function removeItemFromCart(RemoveCartItemService $removeCartItemService, CartItem $cartItem): JsonResponse
    {
        $removeCartItemService->execute($cartItem);

        return response()->json([
            'message' => 'محصول از سبد خرید شما حذف شد.',
        ]);
    }
}
