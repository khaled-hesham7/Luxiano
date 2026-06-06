<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Admin\StoreCategoryRequest;
use App\Http\Requests\Api\Admin\UpdateCategoryRequest;
use App\Http\Resources\CategoryResource;
use App\Models\Category;

class AdminCategoryController extends Controller
{
    // عرض كل الأقسام مع الأقسام الأب والأبناء
    public function index()
    {
        $categories = Category::with(['parent', 'children'])->get();
        return CategoryResource::collection($categories);
    }

    // إضافة قسم جديد
    public function store(StoreCategoryRequest $request)
    {
        $category = Category::create($request->validated());

        return response()->json([
            'message'  => 'تم إضافة القسم بنجاح',
            'category' => new CategoryResource($category)
        ], 201);
    }

    // عرض تفاصيل قسم معين
    public function show($id)
    {
        $category = Category::with(['parent', 'children'])->findOrFail($id);
        return new CategoryResource($category);
    }

    // تعديل قسم موجود
    public function update(UpdateCategoryRequest $request, $id)
    {
        $category = Category::findOrFail($id);
        $category->update($request->validated());

        return response()->json([
            'message'  => 'تم تحديث القسم بنجاح',
            'category' => new CategoryResource($category)
        ], 200);
    }

    // حذف قسم
    public function destroy($id)
    {
        $category = Category::findOrFail($id);
        $category->delete();

        return response()->json([
            'message' => 'تم حذف القسم بنجاح'
        ], 200);
    }
}
