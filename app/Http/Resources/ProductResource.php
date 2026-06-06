<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'category'    => $this->category->name, // اسم القسم
            'name'        => $this->name,
            'slug'        => $this->slug,
            'description' => $this->description,
            'base_price'  => $this->base_price,
            'status'      => $this->status,
            // استخراج الصور وتوفير روابط الـ Conversions
            'images'      => $this->getMedia('images')->map(function ($media) {
                return [
                    'id'       => $media->id,
                    'name'     => $media->file_name,
                    'original' => $media->getUrl(),
                    'medium'   => $media->getUrl('medium'),
                    'thumb'    => $media->getUrl('thumb'),
                ];
            }),
            // استخراج الفيديوهات
            'videos'      => $this->getMedia('videos')->map(function ($media) {
                return [
                    'id'   => $media->id,
                    'name' => $media->file_name,
                    'url'  => $media->getUrl(),
                ];
            }),
            // هنا بنادي على الـ Variants بتاعته لو الفرونت طلبها وعملنالها Eager Loading
            'variants'    => ProductVariantResource::collection($this->whenLoaded('variants')),
        ];
    }
}
