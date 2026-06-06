<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CouponResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'              => $this->id,
            'code'            => $this->code,
            'type'            => $this->type,
            'value'           => (float) $this->value,
            'min_order_value' => (float) $this->min_order_value,
            'start_date'      => $this->start_date->format('Y-m-d H:i:s'),
            'end_date'        => $this->end_date->format('Y-m-d H:i:s'),
            'usage_limit'     => $this->usage_limit,
            'usage_count'     => $this->usage_count,
            'is_active'       => (bool) $this->is_active,
        ];
    }
}
