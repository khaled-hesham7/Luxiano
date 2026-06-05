<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();

            // ربط الطلب بالمستخدم
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            // ربط الطلب بالعنوان اللي هيتشحن عليه
            // استخدمنا restrictOnDelete عشان لو العميل مسح عنوانه القديم من بروفايله، الفاتورة والطلب القديم ميتسحوش من السيستم
            $table->foreignId('address_id')->constrained('addresses')->restrictOnDelete();

            $table->string('order_number')->unique(); // رقم الطلب المميز (LUX-10023)

            // الحسابات المالية للطلب
            $table->decimal('subtotal', 8, 2); // سعر المنتجات بس
            $table->decimal('shipping_cost', 8, 2); // تكلفة الشحن
            $table->decimal('discount', 8, 2)->default(0.00); // قيمة الخصم لو فيه كوبون
            $table->decimal('total', 8, 2); // الإجمالي النهائي المدفوع

            $table->string('payment_method')->default('COD'); // طريقة الدفع: COD, Card
            $table->string('payment_status')->default('pending'); // حالة الدفع: pending, paid, failed
            $table->string('status')->default('pending'); // حالة الطلب: pending, processing, shipped, delivered, canceled

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
