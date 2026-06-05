<?php

namespace App\Models;

use App\Models\OrderItem;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'user_id',
        'address_id',
        'order_number',
        'subtotal',
        'shipping_cost',
        'discount',
        'total',
        'payment_method',
        'payment_status',
        'status'
    ];

    // الطلب ينتمي لمستخدم واحد
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // الطلب شُحن لعنوان واحد
    public function address()
    {
        return $this->belongsTo(Address::class);
    }

    // الطلب يحتوي على العديد من العناصر
    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }
}
