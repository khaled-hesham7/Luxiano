<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * موديل الكوبونات والخصومات.
 * بيحتوي على قواعد التحقق وصلاحية الكوبون وحساب مبالغ الخصم.
 */
class Coupon extends Model
{
    protected $fillable = [
        'code',
        'type',
        'value',
        'min_order_value',
        'start_date',
        'end_date',
        'usage_limit',
        'usage_count',
        'is_active',
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date'   => 'datetime',
        'is_active'  => 'boolean',
    ];

    /**
     * فحص هل الكوبون صالح ومطابق للشروط حالياً أم لا.
     */
    public function isValid(float $subtotal): bool
    {
        $now = now();

        // 1. التأكد من النشاط وصلاحية التواريخ
        if (!$this->is_active || $now->lt($this->start_date) || $now->gt($this->end_date)) {
            return false;
        }

        // 2. التأكد من عدم تخطي حد الاستخدام الأقصى
        if ($this->usage_limit !== null && $this->usage_count >= $this->usage_limit) {
            return false;
        }

        // 3. التأكد من تخطي الحد الأدنى لقيمة الأوردر لتفعيل الكوبون
        if ($subtotal < $this->min_order_value) {
            return false;
        }

        return true;
    }

    /**
     * حساب قيمة الخصم الفعلي بناءً على قيمة المنتجات.
     */
    public function calculateDiscount(float $subtotal): float
    {
        $discount = 0.00;

        if ($this->type === 'percentage') {
            $discount = $subtotal * ($this->value / 100);
        } elseif ($this->type === 'fixed') {
            $discount = $this->value;
        }

        // التأكد من أن قيمة الخصم لا تتجاوز قيمة البضاعة نفسها
        return (float) min($discount, $subtotal);
    }
}
