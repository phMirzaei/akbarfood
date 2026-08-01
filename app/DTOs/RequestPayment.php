<?php

namespace App\DTOs;

use App\Models\Order\Order;

final readonly class RequestPayment
{
    public function __construct(
        public int $userId,
        public Order $order,
    ) {}
}
