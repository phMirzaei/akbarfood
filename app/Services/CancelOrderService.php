<?php

namespace App\Services;

use App\DTOs\CancelOrder;
use App\Exceptions\OrderAlreadyCancelledException;
use App\Exceptions\OrderAlreadyPaidException;
use App\Exceptions\UnauthorizedOrderActionException;
use App\Models\Order\Order;
use App\Models\User;

class CancelOrderService
{
    public function execute(CancelOrder $cancelOrder)
    {
        $order = Order::findOrFail($cancelOrder->orderId);
        $actor = User::findOrFail($cancelOrder->actorId);
        if (! $actor->ownsOrder($order)) {
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
