<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,
            'order_number'   => $this->order_number,
            'status'         => $this->status, // pending, shipped...
            'payment_method' => $this->payment_method,
            'payment_status' => $this->payment_status,

            // الحسابات المالية
            'subtotal'       => $this->subtotal,
            'shipping_cost'  => $this->shipping_cost,
            'discount'       => $this->discount,
            'total'          => $this->total,

            // تفاصيل الشحن (العنوان)
            'shipping_address' => [
                'address'     => $this->address->address_line_1,
                'city'        => $this->address->city,
                'governorate' => $this->address->governorate,
                'phone'       => $this->address->phone,
            ],

            // المنتجات اللي جوه الطلب (بننادي على الـ Resource الفرعي اللي فوق)
            'items'          => OrderItemResource::collection($this->whenLoaded('items')),
            'created_at'     => $this->created_at->format('Y-m-d H:i:s'),
        ];
    }
}
