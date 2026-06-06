<?php

namespace Database\Seeders;
namespace Database\Seeders;

use App\Models\Category;
use App\Models\Attribute;
use App\Models\AttributeValue;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CatalogSeeder extends Seeder
{
    public function run(): void
    {
        // 1. عمل الأقسام الرئيسية والفرعية
        $mens = Category::create(['name' => 'Men', 'slug' => 'men']);
        $womens = Category::create(['name' => 'Women', 'slug' => 'women']);

        $mensShirts = Category::create(['name' => 'Shirts', 'slug' => 'mens-shirts', 'parent_id' => $mens->id]);
        $mensPants = Category::create(['name' => 'Pants', 'slug' => 'mens-pants', 'parent_id' => $mens->id]);

        $womensDresses = Category::create(['name' => 'Dresses', 'slug' => 'womens-dresses', 'parent_id' => $womens->id]);

        // 2. عمل الخصائص وقيمها (Size)
        $sizeAttr = Attribute::create(['name' => 'Size']);
        $sizes = ['S', 'M', 'L', 'XL'];
        $sizeModels = [];
        foreach ($sizes as $size) {
            $sizeModels[$size] = AttributeValue::create(['attribute_id' => $sizeAttr->id, 'value' => $size]);
        }

        // 3. عمل الخصائص وقيمها (Color)
        $colorAttr = Attribute::create(['name' => 'Color']);
        $colors = ['Black', 'White', 'Navy Blue'];
        $colorModels = [];
        foreach ($colors as $color) {
            $colorModels[$color] = AttributeValue::create(['attribute_id' => $colorAttr->id, 'value' => $color]);
        }
    }
}
