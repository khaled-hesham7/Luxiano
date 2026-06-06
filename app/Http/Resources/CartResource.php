<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CartResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        // حساب سعر القطعة (لو الموديل ليه سعر خاص أو السعر الافتراضي للمنتج الأب)
        $unitPrice = $this->price ?? $this->product->base_price;

        // جلب الكمية اللي مبعوتة مع الـ Resource من الكنترولر
        $quantity = $this->resource->cart_quantity;

        return [
            'product_variant_id' => $this->id,
            'product_name'       => $this->product->name,
            'sku'                => $this->sku,
            'quantity'           => $quantity,
            'unit_price'         => $unitPrice,
            'total_item_price'   => $unitPrice * $quantity,
            // بننادي على ريسورس الخصائص اللي عملناه قبل كده للألوان والمقاسات
            'attributes'         => AttributeValueResource::collection($this->whenLoaded('attributeValues')),
        ];
    }
}
