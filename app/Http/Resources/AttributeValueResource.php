<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AttributeValueResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'attribute' => $this->attribute->name, // مثلاً: Color أو Size
            'value'     => $this->value,           // مثلاً: Black أو L
        ];
    }
}
