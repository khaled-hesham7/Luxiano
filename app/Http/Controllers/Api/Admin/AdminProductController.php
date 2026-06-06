<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Admin\StoreProductRequest;
use App\Http\Requests\Api\Admin\UpdateProductRequest;
use App\Http\Resources\ProductResource;
use App\Models\Product;

class AdminProductController extends Controller
{
    // عرض جميع المنتجات مع روابط صورها وفيديوهاتها
    public function index()
    {
        $products = Product::with(['category', 'variants.attributeValues.attribute', 'media', 'variants.media'])->paginate(15);
        return ProductResource::collection($products);
    }

    // إضافة منتج جديد مع رفع ملفات الميديا الخاصة به
    public function store(StoreProductRequest $request)
    {
        $product = Product::create($request->validated());

        // رفع الصور المتعددة
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $imageFile) {
                $product->addMedia($imageFile)->toMediaCollection('images');
            }
        }

        // رفع الفيديو
        if ($request->hasFile('video')) {
            $product->addMedia($request->file('video'))->toMediaCollection('videos');
        }

        return response()->json([
            'message' => 'تم إضافة المنتج بنجاح والوسائط تترفع حالياً',
            'product' => new ProductResource($product->load('media'))
        ], 201);
    }

    // عرض تفاصيل منتج معين
    public function show($id)
    {
        $product = Product::with(['category', 'variants.attributeValues.attribute', 'media', 'variants.media'])->findOrFail($id);
        return new ProductResource($product);
    }

    // تعديل بيانات منتج مع رفع صور أو فيديوهات إضافية
    public function update(UpdateProductRequest $request, $id)
    {
        $product = Product::findOrFail($id);
        $product->update($request->validated());

        // رفع صور إضافية إذا وجدت
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $imageFile) {
                $product->addMedia($imageFile)->toMediaCollection('images');
            }
        }

        // استبدال الفيديو القديم بفيديو جديد إذا تم إرساله
        if ($request->hasFile('video')) {
            $product->clearMediaCollection('videos'); // حذف الفيديوهات القديمة لتوفير المساحة
            $product->addMedia($request->file('video'))->toMediaCollection('videos');
        }

        return response()->json([
            'message' => 'تم تحديث بيانات المنتج بنجاح',
            'product' => new ProductResource($product->load('media'))
        ], 200);
    }

    // حذف منتج وحذف وسائطه تلقائياً من الداتابيز والسيرفر
    public function destroy($id)
    {
        $product = Product::findOrFail($id);
        
        // مسح المنتج وسيقوم Spatie Media Library بمسح الصور والفيديوهات تلقائياً
        $product->delete();

        return response()->json([
            'message' => 'تم حذف المنتج وحذف كافة وسائطه المرفوعة بنجاح'
        ], 200);
    }
}
