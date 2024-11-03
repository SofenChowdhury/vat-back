<?php

namespace App\Models;

use App\Models\Order;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class OrderPayment extends Model
{
    use HasFactory;

    public function orderInfo()
    {
        return $this->belongsTo(Order::class, 'order_id', 'id');
    }
}
