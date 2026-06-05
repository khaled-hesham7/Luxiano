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
        // 1. جدول الخصائص الرئيسية (Attributes)
        Schema::create('attributes', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // مثل: Color ، Size
            $table->timestamps();
        });

        // 2. جدول قيم الخصائص (Attribute Values)
        Schema::create('attribute_values', function (Blueprint $table) {
            $table->id();

            // ربط القيمة بالخاصية بتاعتها (مثلاً القيمة "L" مربوطة بـ "Size")
            $table->foreignId('attribute_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('value'); // مثل: Red, Black, S, M, L, XL
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attributes_and_attribute_values');
    }
};
