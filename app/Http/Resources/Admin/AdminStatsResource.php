<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdminStatsResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'financials' => [
                'total_sales' => (float) $this['total_sales'],
            ],
            'orders_count' => [
                'pending'   => $this['pending_orders'],
                'completed' => $this['completed_orders'],
            ],
            'top_selling_products' => $this['top_products'],
        ];
    }
}
