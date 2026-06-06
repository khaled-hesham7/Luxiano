<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Admin\StoreVariantRequest;
use App\Http\Requests\Api\Admin\UpdateVariantRequest;
use App\Http\Resources\ProductVariantResource;
use App\Models\Product;
use App\Models\ProductVariant;

class AdminProductVariantController extends Controller
{
    // إضافة Variant جديد لمنتج معين
    public function store(StoreVariantRequest $request, $product_id)
    {
        $product = Product::findOrFail($product_id);

        $variant = $product->variants()->create([
            'sku'   => $request->sku,
            'price' => $request->price,
            'stock' => $request->stock,
        ]);

        // ربط الخصائص (مقاس، لون) بالجدول الوسيط
        $variant->attributeValues()->sync($request->attribute_value_ids);

        // رفع صور الـ Variant
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $imageFile) {
                $variant->addMedia($imageFile)->toMediaCollection('variant_images');
            }
        }

        return response()->json([
            'message' => 'تم إضافة موديل المنتج بنجاح وتخصيص الخصائص والوسائط له',
            'variant' => new ProductVariantResource($variant->load(['attributeValues.attribute', 'media']))
        ], 201);
    }

    // تعديل Variant موجود
    public function update(UpdateVariantRequest $request, $id)
    {
        $variant = ProductVariant::findOrFail($id);

        $variant->update($request->only(['sku', 'price', 'stock']));

        // تعديل قيم الخصائص لو تم إرسالها
        if ($request->has('attribute_value_ids')) {
            $variant->attributeValues()->sync($request->attribute_value_ids);
        }

        // رفع صور جديدة للـ Variant
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $imageFile) {
                $variant->addMedia($imageFile)->toMediaCollection('variant_images');
            }
        }

        return response()->json([
            'message' => 'تم تحديث بيانات الموديل بنجاح',
            'variant' => new ProductVariantResource($variant->load(['attributeValues.attribute', 'media']))
        ], 200);
    }

    // حذف Variant
    public function destroy($id)
    {
        $variant = ProductVariant::findOrFail($id);
        $variant->delete(); // مسح العلاقة والوسائط تلقائياً

        return response()->json([
            'message' => 'تم حذف موديل المنتج بنجاح'
        ], 200);
    }
}
