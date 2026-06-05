<?php

namespace App\Models;

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductVariant extends Model
{
    protected $fillable = ['product_id', 'sku', 'price', 'stock'];

    // الموديل الفرعي ينتمي لمنتج أب أساسي
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    // الموديل الفرعي مربوط بقيم خصائص (لون ومقاس) عبر الجدول الوسيط
    public function attributeValues()
    {
        return $this->belongsToMany(AttributeValue::class, 'variant_attribute_value');
    }
}
