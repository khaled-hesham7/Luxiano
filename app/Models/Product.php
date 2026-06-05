<?php

namespace App\Models;

use App\Models\Category;
use App\Models\ProductVariant;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
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
}
