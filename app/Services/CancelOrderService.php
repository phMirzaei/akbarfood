<?php

namespace App\Services;

use App\DTOs\CancelOrder;
use App\Exceptions\OrderAlreadyCancelledException;
use App\Exceptions\OrderAlreadyPaidException;
use App\Exceptions\UnauthorizedOrderActionException;
use App\Models\Order\Order;

class CancelOrderService
{
    public function execute(CancelOrder $cancelOrder)
    {
        $order = Order::findOrFail($cancelOrder->orderId);
        if ($order->user_id !== $cancelOrder->userId) {
            throw new UnauthorizedOrderActionException;
        }
        if ($order->isPaid()) {
            throw new OrderAlreadyPaidException;
        }
        if ($order->isCancelled()) {
            throw new OrderAlreadyCancelledException;
        }
        $order->cancel();
        $order->save();
    }
}
