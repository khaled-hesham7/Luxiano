<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_variants', function (Blueprint $table) {
            $table->id();

            // ربط الموديل بالمنتج الأب
            $table->foreignId('product_id')
                ->constrained()
                ->cascadeOnDelete();

            // كود التخزين الفريد (مهم جداً للمخازن والـ التتبع) مثل: LUX-SHIRT-BLK-L
            $table->string('sku')->unique();

            // السعر الخاص بالموديل ده (لو المقاسات الكبيرة أو ألوان معينة سعرها مختلف)
            // حطيناه nullable عشان لو فاضي، السيستم ياخد الـ base_price من جدول المنتج الأساسي
            $table->decimal('price', 8, 2)->nullable();

            // كمية المخزن المتاحة للبيع من القطعة دي بالظبط
            $table->integer('stock')->default(0);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_variants');
    }
};
