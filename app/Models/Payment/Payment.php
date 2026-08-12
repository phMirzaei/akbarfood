<?php

namespace App\Models\Payment;

use App\Models\Order\Order;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    protected $fillable = ['order_id', 'transaction_id', 'amount', 'status', 'paid_at', 'created_at', 'updated_at'];

    public function Order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }

    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    public function markAsPaid(string $transactionId): void
    {
        $this->status = 'paid';
        $this->transaction_id = $transactionId;
        $this->paid_at = now();
    }
}
