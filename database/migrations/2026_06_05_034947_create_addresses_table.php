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
        Schema::create('addresses', function (Blueprint $table) {
            $table->id();

            // ربط العنوان بالمستخدم (العميل)
            // استخدمنا cascadeOnDelete عشان لو حساب العميل اتحذف، عناوينه تتمسح معاه
            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('address_line_1'); // العنوان بالتفصيل (اسم الشارع، رقم العمارة، الشقة)
            $table->string('address_line_2')->nullable(); // علامة مميزة أو تفاصيل إضافية (اختياري)

            // المحافظة والمدينة مهمين جداً عشان حساب تكلفة الشحن بعدين
            $table->string('city'); // المدينة / المنطقة (مثلاً: الدقي، مدينة نصر)
            $table->string('governorate'); // المحافظة (مثلاً: الجيزة، القاهرة)

            $table->string('phone'); // رقم تليفون المستلم الخاص بالشحنة دي بالذات

            // حقل لتحديد هل ده العنوان الأساسي للعميل ولا لأ
            // عشان أول ما يفتح الـ Checkout نختارهوله تلقائي
            $table->boolean('is_default')->default(false);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('addresses');
    }
};
