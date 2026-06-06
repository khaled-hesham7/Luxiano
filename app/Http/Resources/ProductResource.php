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
            // هنا بنادي على الـ Variants بتاعته لو الفرونت طلبها وعملنالها Eager Loading
            'variants'    => ProductVariantResource::collection($this->whenLoaded('variants')),
        ];
    }
}
