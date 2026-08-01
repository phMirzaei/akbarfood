<?php

namespace App\Http\Controllers\Order;

use App\DTOs\CreateOrder;
use App\DTOs\ListOrder;
use App\Http\Controllers\Controller;
use App\Services\CreateOrderService;
use App\Services\ListOrderService;
use Illuminate\Http\JsonResponse;

class OrderController extends Controller
{
    public function createOrder(CreateOrderService $createOrderService): JsonResponse
    {
        $createOrderService->execute(new CreateOrder(auth()->id()));

        return response()->json([
            'message' => 'سفارش شما ثبت و در انتظار پرداخت می باشد.',
        ]);
    }

    public function listOrder(ListOrderService $listOrderService): JsonResponse
    {
        return response()->json(
            $listOrderService->execute(new ListOrder(auth()->id())),
        );
    }
}
