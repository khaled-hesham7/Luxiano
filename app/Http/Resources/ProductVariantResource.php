<?php



namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductVariantResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'               => $this->id,
            'sku'              => $this->sku,
            'stock'            => $this->stock,
            // لو الموديل ليه سعر خاص هيرجع، لو Null هيرجع السعر الأساسي للمنتج
            'price'            => $this->price ?? $this->product->base_price,
            // من هنا بنجيب الألوان والمقاسات المربوطة بالـ Variant ده بالذات
            'attributes'       => AttributeValueResource::collection($this->whenLoaded('attributeValues')),
        ];
    }
}
