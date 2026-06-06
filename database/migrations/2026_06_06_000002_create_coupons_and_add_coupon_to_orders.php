<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. إنشاء جدول الكوبونات
        Schema::create('coupons', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // كود الخصم (مثل: LUX10)
            $table->string('type')->default('percentage'); // fixed أو percentage
            $table->decimal('value', 8, 2); // قيمة الخصم (10% أو 50 جنيه)
            $table->decimal('min_order_value', 8, 2)->default(0.00); // الحد الأدنى لتفعيل الخصم
            $table->dateTime('start_date');
            $table->dateTime('end_date');
            $table->integer('usage_limit')->nullable(); // أقصى عدد مرات استخدام للكوبون ككل
            $table->integer('usage_count')->default(0); // عدد مرات الاستخدام الفعلية
            $table->boolean('is_active')->default(true); // نشط أم لا
            $table->timestamps();
        });

        // 2. تحديث جدول الأوردرات لإضافة حقل الكود المستخدم
        Schema::table('orders', function (Blueprint $table) {
            $table->string('coupon_code')->nullable()->after('order_number');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('coupon_code');
        });
        
        Schema::dropIfExists('coupons');
    }
};
