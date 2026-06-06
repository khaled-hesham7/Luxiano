<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Admin\StoreShippingRateRequest;
use App\Http\Requests\Api\Admin\UpdateShippingRateRequest;
use App\Http\Resources\ShippingRateResource;
use App\Models\ShippingRate;

class AdminShippingRateController extends Controller
{
    // عرض قائمة أسعار شحن المحافظات
    public function index()
    {
        $rates = ShippingRate::orderBy('governorate', 'asc')->get();
        return ShippingRateResource::collection($rates);
    }

    // إضافة سعر شحن لمحافظة جديدة
    public function store(StoreShippingRateRequest $request)
    {
        $data = $request->validated();
        // نحفظ المحافظة بحروف صغيرة لضمان سهولة مطابقتها لاحقاً
        $data['governorate'] = strtolower(trim($data['governorate']));

        $rate = ShippingRate::create($data);

        return response()->json([
            'message' => 'تم تسجيل سعر شحن المحافظة بنجاح',
            'rate'    => new ShippingRateResource($rate)
        ], 201);
    }

    // عرض سعر شحن محافظة محددة
    public function show($id)
    {
        $rate = ShippingRate::findOrFail($id);
        return new ShippingRateResource($rate);
    }

    // تعديل سعر الشحن أو المحافظة
    public function update(UpdateShippingRateRequest $request, $id)
    {
        $rate = ShippingRate::findOrFail($id);
        $data = $request->validated();

        if (isset($data['governorate'])) {
            $data['governorate'] = strtolower(trim($data['governorate']));
        }

        $rate->update($data);

        return response()->json([
            'message' => 'تم تحديث سعر الشحن بنجاح',
            'rate'    => new ShippingRateResource($rate)
        ], 200);
    }

    // حذف سعر الشحن لمحافظة
    public function destroy($id)
    {
        $rate = ShippingRate::findOrFail($id);
        $rate->delete();

        return response()->json([
            'message' => 'تم حذف سعر الشحن للمحافظة بنجاح'
        ], 200);
    }
}
