<?php

namespace App\Http\Controllers\Cart;

use App\DTOs\AddItemToCart;
use App\DTOs\RemoveCartItem;
use App\DTOs\UpdateCartItem;
use App\Http\Controllers\Controller;
use App\Http\Requests\Cart\AddItemToCartRequest;
use App\Http\Requests\Cart\UpdateCartItemRequest;
use App\Models\Cart\CartItem;
use App\Models\Restaurant\Restaurant;
use App\Services\AddItemToCartService;
use App\Services\RemoveCartItemService;
use App\Services\UpdateCartItemService;
use Illuminate\Http\JsonResponse;

class CartItemController extends Controller
{
    public function addItemToCart(AddItemToCartRequest $request, AddItemToCartService $addItemToCartService, Restaurant $restaurant): JsonResponse
    {

        $cartItem = new AddItemToCart(
            userId: auth()->id(),
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
            cartItemId: $cartItem->id,
            userId: auth()->id()
        );
        $updateCartItemService->execute($UpdatedCartItem);

        return response()->json([
            'message' => 'آیتم ویرایش شد.',
        ]);
    }

    public function removeItemFromCart(RemoveCartItemService $removeCartItemService, CartItem $cartItem): JsonResponse
    {
        $removeCartItem = new RemoveCartItem(
            cartItemId: $cartItem->id,
            userId: auth()->id(),
        );
        $removeCartItemService->execute($removeCartItem);

        return response()->json([
            'message' => 'محصول از سبد خرید شما حذف شد.',
        ]);
    }
}
