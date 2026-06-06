<?php

namespace App\Models;

use App\Models\Category;
use App\Models\ProductVariant;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Product extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $fillable = ['category_id', 'name', 'slug', 'description', 'base_price', 'status'];

    // المنتج ينتمي لقسم معين
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    // المنتج الواحد ليه موديلات كتير (مقاسات وألوان)
    public function variants()
    {
        return $this->hasMany(ProductVariant::class);
    }

    // تسجيل الكولكشنز للصور والفيديوهات
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('images'); // بيدعم صور متعددة تلقائياً
        $this->addMediaCollection('videos'); // بيدعم فيديوهات متعددة تلقائياً
    }

    // تحويلات الصور للويب والموبايل
    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->width(150)
            ->height(150)
            ->sharpen(10)
            ->nonQueued(); // غير مجدول عشان يتعمل فوراً أثناء الرفع

        $this->addMediaConversion('medium')
            ->width(600)
            ->height(600)
            ->sharpen(10)
            ->nonQueued();
    }
}
