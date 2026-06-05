<?php

namespace App\Models;

use App\Models\Order;
use App\Models\ProductVariant;
use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    protected $fillable = ['order_id', 'product_variant_id', 'quantity', 'price'];

    // ينتمي لطلب رئيسي
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    // يشاور على الموديل المحدد (اللون والمقاس)
    public function variant()
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }
}
