<?php

namespace App\Http\Controllers\Order;

use App\Http\Controllers\Controller;
use App\Models\Order\Order;
use App\Services\CreateOrderService;
use Illuminate\Http\JsonResponse;

class OrderController extends Controller
{
    public function createOrder(CreateOrderService $createOrderService): JsonResponse
    {
        return response()->json(
            $createOrderService->execute(),
        );
    }

    public function listOrder()
    {
        $order = Order::where('user_id', auth()->id())->get();

        return response()->json(
            $order->load('items')
        );
    }
}
