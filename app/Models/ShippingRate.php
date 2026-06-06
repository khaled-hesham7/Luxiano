<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * موديل أسعار الشحن للمحافظات.
 * بيحفظ تكلفة الشحن المخصصة لكل محافظة يتم إدارتها من الأدمن.
 */
class ShippingRate extends Model
{
    protected $fillable = ['governorate', 'cost'];
}
