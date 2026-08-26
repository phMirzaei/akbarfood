<?php

namespace App\Services;

use App\DTOs\ListOrder;
use App\Http\Resources\OrderResource;
use App\Models\Order\Order;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ListOrderService
{
    public function execute(ListOrder $listOrder): AnonymousResourceCollection
    {
        return OrderResource::collection(
            Order::with('items')
                ->where('user_id', $listOrder->actorId)
                ->get()
        );
    }
}
