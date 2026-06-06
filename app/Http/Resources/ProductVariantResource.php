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
            // صور الموديل المخصصة
            'images'           => $this->getMedia('variant_images')->map(function ($media) {
                return [
                    'id'       => $media->id,
                    'name'     => $media->file_name,
                    'original' => $media->getUrl(),
                    'medium'   => $media->getUrl('medium'),
                    'thumb'    => $media->getUrl('thumb'),
                ];
            }),
            // من هنا بنجيب الألوان والمقاسات المربوطة بالـ Variant ده بالذات
            'attributes'       => AttributeValueResource::collection($this->whenLoaded('attributeValues')),
        ];
    }
}
