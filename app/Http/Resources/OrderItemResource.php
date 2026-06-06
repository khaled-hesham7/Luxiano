<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                 => $this->id,
            'product_variant_id' => $this->product_variant_id,
            'product_name'       => $this->variant->product->name, // جلب اسم المنتج الأب
            'sku'                => $this->variant->sku,
            'quantity'           => $this->quantity,
            'price_at_purchase'  => $this->price, // السعر اللي العميل اشترى بيه فعلياً
            'total_item_price'   => $this->price * $this->quantity,
            // نرجع المقاس واللون بتوع القطعة دي
            'attributes'         => AttributeValueResource::collection($this->variant->attributeValues),
        ];
    }
}
