<?php

use App\Http\Controllers\Api\AddressController;
use App\Http\Controllers\Api\Admin\AdminCategoryController;
use App\Http\Controllers\Api\Admin\AdminDashboardController;
use App\Http\Controllers\Api\Admin\AdminOrderController;
use App\Http\Controllers\Api\Admin\AdminProductController;
use App\Http\Controllers\Api\Admin\AdminProductVariantController;
use App\Http\Controllers\Api\Admin\AdminShippingRateController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\ProductController;
use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpFoundation\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
| هنا بنعرف كل مسارات (Routes) الـ API الخاصة بالموقع.
| كل المسارات دي بتاخد prefix تلقائي '/api'.
*/

// ==========================================
// 1. المسارات العامة (Public Routes) - مفتوحة للكل بدون تسجيل دخول
// ==========================================

// مسارات تسجيل الحساب والدخول
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// مسارات تصفح الكتالوج والمنتجات
Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/{slug}', [ProductController::class, 'show']);

// مسارات سلة التسوق (بتعتمد على الـ IP للزائر أو الـ ID للمسجل)
Route::get('/cart', [CartController::class, 'index']);
Route::post('/cart', [CartController::class, 'store']);
Route::delete('/cart/{variant_id}', [CartController::class, 'destroy']);


// ==========================================
// 2. مسارات العملاء المسجلين (Authenticated Customer Routes) - محتاجة Token
// ==========================================
Route::middleware('auth:sanctum')->group(function () {
    
    // تسجيل الخروج وجلب بيانات العميل الحالي
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', function (Request $request) {
        return new \App\Http\Resources\UserResource($request->user());
    });

    // إتمام طلب الشراء
    Route::post('/orders', [OrderController::class, 'store']);

    // إدارة عناوين الشحن الخاصة بالعميل (CRUD)
    Route::apiResource('addresses', AddressController::class);
});


// ==========================================
// 3. مسارات لوحة التحكم للأدمن (Admin Dashboard Routes) - محتاجة Token وصلاحية أدمن
// ==========================================
Route::middleware(['auth:sanctum', 'admin'])->prefix('admin')->group(function () {

    // إحصائيات لوحة التحكم والداش بورد
    Route::get('/dashboard/stats', [AdminDashboardController::class, 'index']);

    // إدارة وفحص طلبات العملاء وتعديل حالتها
    Route::get('/orders', [AdminOrderController::class, 'index']);
    Route::put('/orders/{id}/status', [AdminOrderController::class, 'updateStatus']);

    // إدارة الكتالوج والأقسام والمنتجات والـ Media
    Route::apiResource('categories', AdminCategoryController::class);
    Route::apiResource('products', AdminProductController::class);
    
    // إدارة موديلات المنتجات (Product Variants) وصورها المخصصة
    Route::post('products/{product_id}/variants', [AdminProductVariantController::class, 'store']);
    Route::put('variants/{id}', [AdminProductVariantController::class, 'update']);
    Route::delete('variants/{id}', [AdminProductVariantController::class, 'destroy']);

    // إدارة أسعار الشحن للمحافظات ديناميكياً
    Route::apiResource('shipping-rates', AdminShippingRateController::class);
});
