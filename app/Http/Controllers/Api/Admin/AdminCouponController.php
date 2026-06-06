<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Admin\StoreCouponRequest;
use App\Http\Requests\Api\Admin\UpdateCouponRequest;
use App\Http\Resources\CouponResource;
use App\Models\Coupon;

class AdminCouponController extends Controller
{
    // عرض قائمة الكوبونات المتوفرة للأدمن
    public function index()
    {
        $coupons = Coupon::orderBy('created_at', 'desc')->paginate(15);
        return CouponResource::collection($coupons);
    }

    // إنشاء كوبون خصم جديد
    public function store(StoreCouponRequest $request)
    {
        $coupon = Coupon::create($request->validated());

        return response()->json([
            'message' => 'تم إنشاء كوبون الخصم بنجاح',
            'coupon'  => new CouponResource($coupon)
        ], 201);
    }

    // عرض تفاصيل كوبون معين
    public function show($id)
    {
        $coupon = Coupon::findOrFail($id);
        return new CouponResource($coupon);
    }

    // تعديل بيانات الكوبون
    public function update(UpdateCouponRequest $request, $id)
    {
        $coupon = Coupon::findOrFail($id);
        $coupon->update($request->validated());

        return response()->json([
            'message' => 'تم تحديث بيانات الكوبون بنجاح',
            'coupon'  => new CouponResource($coupon)
        ], 200);
    }

    // حذف الكوبون نهائياً
    public function destroy($id)
    {
        $coupon = Coupon::findOrFail($id);
        $coupon->delete();

        return response()->json([
            'message' => 'تم حذف الكوبون بنجاح'
        ], 200);
    }
}
