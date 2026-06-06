<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\AttributeValue;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        // هنجيب قسم القمصان وقيم الخصائص اللي عملناها في السيرفر الأول
        $category = Category::where('slug', 'mens-shirts')->first();
        $sizes = AttributeValue::whereHas('attribute', function ($q) {
            $q->where('name', 'Size');
        })->get();
        $colors = AttributeValue::whereHas('attribute', function ($q) {
            $q->where('name', 'Color');
        })->get();

        // 1. تكريت المنتج الأب
        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Oxford Slim Fit Shirt',
            'slug' => 'oxford-slim-fit-shirt',
            'description' => 'Premium cotton oxford shirt, perfect for casual and formal wear.',
            'base_price' => 850.00,
            'status' => 'active'
        ]);

        // 2. عمل الـ Variants تلقائياً (توليفة كل لون مع كل مقاس)
        foreach ($colors as $color) {
            foreach ($sizes as $size) {
                // عمل الموديل الفرعي في المخزن
                $variant = ProductVariant::create([
                    'product_id' => $product->id,
                    'sku' => strtoupper("LUX-SHRT-{$color->value}-{$size->value}"),
                    'price' => null, // هيورث السعر الأساسي (850)
                    'stock' => rand(5, 20) // كمية عشوائية في المخزن بين 5 لـ 20 قطعة
                ]);

                // ربط الموديل بالقيم في الجدول الوسيط variant_attribute_value
                $variant->attributeValues()->attach([$color->id, $size->id]);
            }
        }
    }
}
