<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // اسم القسم (رجالي، حريمي، قمصان)
            $table->string('slug')->unique(); // الرابط اللطيف للـ URL

            // الربط الذاتي لعمل أقسام فرعية (مثلاً: قمصان تابعة لقسم رجالي)
            // استخدام cascadeOnDelete عشان لو مسحنا القسم الرئيسي، الفرعي يتمسح أو يتهندل
            $table->foreignId('parent_id')
                ->nullable()
                ->constrained('categories')
                ->nullOnDelete();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
