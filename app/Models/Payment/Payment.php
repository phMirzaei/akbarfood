<?php

namespace App\Models\Payment;

use App\Enums\PaymentStatus;
use App\Models\Order\Order;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    protected $fillable = ['order_id', 'transaction_id', 'amount', 'status', 'paid_at', 'created_at', 'updated_at'];

    protected function casts(): array
    {
        return [
            'status' => PaymentStatus::class,
        ];
    }

    public function Order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function isPaid(): bool
    {
        return $this->status === PaymentStatus::Paid;
    }

    public function isFailed(): bool
    {
        return $this->status === PaymentStatus::Failed;
    }

    public function markAsPaid(string $transactionId): void
    {
        $this->status = PaymentStatus::Paid;
        $this->transaction_id = $transactionId;
        $this->paid_at = now();
    }
}
