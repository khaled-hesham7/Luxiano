<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Models\Category;

class CategoryController extends Controller
{
    // عرض قائمة الأقسام الرئيسية وأقسامها الفرعية لتصميم الـ Navbar
    public function index()
    {
        $categories = Category::with('children')
            ->whereNull('parent_id') // جلب الأقسام الأساسية فقط
            ->get();

        return CategoryResource::collection($categories);
    }
}
