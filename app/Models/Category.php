<?php

namespace App\Models;

use App\Models\Product;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $fillable = ['name', 'slug', 'parent_id'];

    // القسم يحتوي على منتجات كثيرة
    public function products()
    {
        return $this->hasMany(Product::class);
    }

    // لجلب الأقسام الفرعية التابعة للقسم ده
    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    // لمعرفة القسم الأب للقسم الحالي
    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }
}
