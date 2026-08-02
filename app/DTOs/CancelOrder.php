<?php

namespace App\DTOs;

use App\Models\Order\Order;

final readonly class CancelOrder
{
    public function __construct(
        public Order $order,
        public int $userId,
    ) {}
}
