<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
    protected $fillable = ['user_id', 'address_line_1', 'address_line_2', 'city', 'governorate', 'phone', 'is_default'];

    // العنوان ينتمي لمستخدم واحد
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // العنوان الواحد ممكن يتشحن عليه أكتر من طلب للمستخدم ده
    public function orders()
    {
        return $this->hasMany(Order::class);
    }
}
