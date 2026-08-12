<?php

namespace App\Services;

use App\DTOs\CancelOrder;
use App\Exceptions\OrderAlreadyCancelledException;
use App\Exceptions\OrderAlreadyPaidException;
use App\Exceptions\UnauthorizedOrderActionException;

class CancelOrderService
{
    public function execute(CancelOrder $cancelOrder)
    {
        if ($cancelOrder->order->user_id !== $cancelOrder->userId) {
            throw new UnauthorizedOrderActionException;
        }
        if ($cancelOrder->order->status == 'paid') {
            throw new OrderAlreadyPaidException;
        }
        if ($cancelOrder->order->status == 'cancelled') {
            throw new OrderAlreadyCancelledException;
        }
        $cancelOrder->order->update([
            'status' => 'cancelled',
        ]);
    }
}
