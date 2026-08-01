<?php

namespace App\Services;

use App\DTOs\ListOrder;
use App\Models\Order\Order;

class ListOrderService
{
    public function execute(ListOrder $listOrder)
    {
        return Order::with('items')
            ->where('user_id', $listOrder->userId)
            ->get();
    }
}
