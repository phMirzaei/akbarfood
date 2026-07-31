<?php

namespace App\Models\Order;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    protected $fillable = ['order_id', 'menu_id', 'unit_price', 'name', 'quantity', 'created_at', 'updated_at'];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
