<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreAddressRequest;
use App\Http\Requests\Api\UpdateAddressRequest;
use App\Http\Resources\AddressResource;
use App\Models\Address;
use Illuminate\Http\Request;

class AddressController extends Controller
{
    // عرض جميع عناوين العميل الحالي
    public function index(Request $request)
    {
        $addresses = $request->user()->addresses()->orderBy('is_default', 'desc')->get();
        return AddressResource::collection($addresses);
    }

    // إضافة عنوان جديد للعميل الحالي
    public function store(StoreAddressRequest $request)
    {
        // لو العنوان ده افتراضي، شيل الافتراضي من العناوين التانية
        if ($request->is_default) {
            $request->user()->addresses()->update(['is_default' => false]);
        }

        $address = $request->user()->addresses()->create($request->validated());

        return response()->json([
            'message' => 'تم إضافة العنوان بنجاح',
            'address' => new AddressResource($address)
        ], 201);
    }

    // عرض عنوان محدد للعميل الحالي
    public function show(Request $request, $id)
    {
        $address = $request->user()->addresses()->findOrFail($id);
        return new AddressResource($address);
    }

    // تعديل العنوان الخاص بالعميل الحالي
    public function update(UpdateAddressRequest $request, $id)
    {
        $address = $request->user()->addresses()->findOrFail($id);

        // لو العنوان ده افتراضي، شيل الافتراضي من العناوين التانية
        if ($request->is_default) {
            $request->user()->addresses()->update(['is_default' => false]);
        }

        $address->update($request->validated());

        return response()->json([
            'message' => 'تم تحديث العنوان بنجاح',
            'address' => new AddressResource($address)
        ], 200);
    }

    // حذف عنوان يخص العميل الحالي
    public function destroy(Request $request, $id)
    {
        $address = $request->user()->addresses()->findOrFail($id);
        $address->delete();

        return response()->json([
            'message' => 'تم حذف العنوان بنجاح'
        ], 200);
    }
}
