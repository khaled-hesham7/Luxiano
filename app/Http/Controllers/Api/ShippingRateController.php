<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ShippingRateResource;
use App\Models\ShippingRate;

class ShippingRateController extends Controller
{
    // عرض أسعار شحن المحافظات للعملاء والـ Checkout
    public function index()
    {
        $rates = ShippingRate::orderBy('governorate', 'asc')->get();
        return ShippingRateResource::collection($rates);
    }
}
