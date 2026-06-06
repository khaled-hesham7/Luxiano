<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class ProductVariant extends Model implements HasMedia
{
    use InteractsWithMedia;

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

    // تسجيل ميديا كولكشن لصور الـ Variant
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('variant_images');
    }

    // تحويلات صور الـ Variant
    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->width(150)
            ->height(150)
            ->sharpen(10)
            ->nonQueued();

        $this->addMediaConversion('medium')
            ->width(600)
            ->height(600)
            ->sharpen(10)
            ->nonQueued();
    }
}
