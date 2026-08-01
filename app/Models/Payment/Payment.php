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
}
