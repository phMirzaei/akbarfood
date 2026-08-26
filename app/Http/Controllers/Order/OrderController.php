<?php

namespace App\Http\Controllers\Order;

use App\DTOs\CancelOrder;
use App\DTOs\CreateOrder;
use App\DTOs\ListOrder;
use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use App\Models\Order\Order;
use App\Models\Restaurant\Restaurant;
use App\Services\CancelOrderService;
use App\Services\CreateOrderService;
use App\Services\ListOrderService;
use Illuminate\Http\JsonResponse;

class OrderController extends Controller
{
    public function createOrder(CreateOrderService $createOrderService, Restaurant $restaurant): JsonResponse
    {
        $createOrderService->execute(new CreateOrder(
            userId: auth()->id(),
            restaurantId: $restaurant->id
        ));

        return response()->json([
            'message' => 'سفارش شما ثبت و در انتظار پرداخت می باشد.',
        ]);
    }

    public function listOrder(ListOrderService $listOrderService): JsonResponse
    {
        return response()->json(
            OrderResource::collection(
                $listOrderService->execute(new ListOrder(
                    actorId: auth()->id(),
                )),
            )
        );
    }

    public function cancelOrder(CancelOrderService $cancelOrderService, Order $order): JsonResponse
    {
        $cancelOrder = new CancelOrder(
            orderId: $order->id,
            actorId: auth()->id()
        );
        $cancelOrderService->execute($cancelOrder);

        return response()->json([
            'message' => 'سفارش شما لغو شد.',
        ]);
    }
}
