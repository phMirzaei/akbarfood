<?php

namespace App\Services;

use App\DTOs\CancelOrder;
use App\Exceptions\AuthorizationException;
use App\Exceptions\OrderAlreadyPaidException;

class CancelOrderService
{
    public function execute(CancelOrder $cancelOrder)
    {
        if ($cancelOrder->order->user_id !== $cancelOrder->userId) {
            throw new AuthorizationException;
        }
        if ($cancelOrder->order->status !== 'pending') {
            throw new OrderAlreadyPaidException;
        }
        $cancelOrder->order->update([
            'status' => 'cancelled',
        ]);
    }
}
