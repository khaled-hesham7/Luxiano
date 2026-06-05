<?php

namespace App\Models;

use App\Models\ProductVariant;
use Illuminate\Database\Eloquent\Model;

class AttributeValue extends Model
{
    protected $fillable = ['attribute_id', 'value'];

    // القيمة بتنتمي لخاصية رئيسية واحدة
    public function attribute()
    {
        return $this->belongsTo(Attribute::class);
    }

    // القيمة الواحدة (زي اللون الأسود) ممكن تتربط بكذا Variant لمنتجات مختلفة
    public function variants()
    {
        return $this->belongsToMany(ProductVariant::class, 'variant_attribute_value');
    }
}
