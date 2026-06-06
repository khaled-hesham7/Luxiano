<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\ProductFilterRequest;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    // 1. عرض كل المنتجات مع دعم الفلترة والبحث
    public function index(ProductFilterRequest $request)
    {
        // استخدام الـ Eager Loading لمنع مشكلة الـ N+1 Query الشهيرة
        $query = Product::with(['category', 'variants.attributeValues.attribute'])->where('status', 'active');

        // فلترة بالقسم
        if ($request->has('category_slug')) {
            $query->whereHas('category', function ($q) use ($request) {
                $q->where('slug', $request->category_slug);
            });
        }

        // فلترة بأقل سعر وأعلى سعر
        if ($request->has('min_price')) {
            $query->where('base_price', '>=', $request->min_price);
        }
        if ($request->has('max_price')) {
            $query->where('base_price', '<=', $request->max_price);
        }

        // بحث باسم المنتج أو وصفه
        if ($request->has('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                    ->orWhere('description', 'like', '%' . $request->search . '%');
            });
        }

        // جلب الداتا مع عمل Pagination (عشر منتجات في كل صفحة)
        $products = $query->paginate(10);

        return ProductResource::collection($products);
    }

    // 2. عرض تفاصيل منتج واحد محدد بالـ Variants والمخزن بتاعه
    public function show($slug)
    {
        $product = Product::with(['category', 'variants.attributeValues.attribute'])
            ->where('slug', $slug)
            ->where('status', 'active')
            ->firstOrFail();

        return new ProductResource($product);
    }
}
