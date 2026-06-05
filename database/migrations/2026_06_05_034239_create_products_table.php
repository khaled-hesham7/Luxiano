<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();

            // ربط المنتج بالقسم بتاعه
            $table->foreignId('category_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('name'); // اسم المنتج الأساسي (قميص أكسفورد)
            $table->string('slug')->unique();
            $table->text('description')->nullable(); // وصف الخامة والتفاصيل

            // السعر الأساسي (8 خانات منهم 2 بعد العلامة العشرية)
            $table->decimal('base_price', 8, 2);

            // حالة المنتج (نشط، مسودة، غير متوفر) لتسهيل العرض في الفرونت
            $table->string('status')->default('active');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
