<?php

use App\Http\Controllers\Api\Admin\AdminDashboardController;
use App\Http\Controllers\Api\Admin\AdminOrderController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\ProductController;
use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpFoundation\Request;

// المسارات العامة (مفتوحة لأي حد)
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// المسارات المحمية (لازم يكون العميل معاه Token وصلاحية)
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);

    // تجربة جلب بيانات العميل الحالي المسجل
    Route::get('/me', function (Request $request) {
        return new \App\Http\Resources\UserResource($request->user());
    });


    Route::post('/orders', [OrderController::class, 'store']);







});




Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/{slug}', [ProductController::class, 'show']);





Route::get('/cart', [CartController::class, 'index']);
Route::post('/cart', [CartController::class, 'store']);
Route::delete('/cart/{variant_id}', [CartController::class, 'destroy']);





Route::middleware(['auth:sanctum', 'admin'])->prefix('admin')->group(function () {

    // 1. مسار إحصائيات الداش بورد
    Route::get('/dashboard/stats', [AdminDashboardController::class, 'index']);

    // 2. مسارات إدارة الطلبات
    Route::get('/orders', [AdminOrderController::class, 'index']);
    Route::put('/orders/{id}/status', [AdminOrderController::class, 'updateStatus']);
});
