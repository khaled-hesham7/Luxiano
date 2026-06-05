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
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();

            // بنربط بالـ variant مباشرة عشان نعرف المقاس واللون اللي اتباعوا
            $table->foreignId('product_variant_id')->constrained('product_variants')->restrictOnDelete();

            $table->integer('quantity'); // الكمية المطلوبة من القطعة دي
            $table->decimal('price', 8, 2); // سعر القطعة وقت الشراء (مهم جداً عشان لو السعر اتغير في الكتالوج بعدين)

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
