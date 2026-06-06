<?php

namespace App\Http\Requests\Api;

use App\Models\ProductVariant;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class CartStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // تفعيل الـ Request
    }

    public function rules(): array
    {
        return [
            'product_variant_id' => 'required|exists:product_variants,id',
            'quantity'           => [
                'required',
                'integer',
                'min:1',
                // كلوز مخصص بيتأكد إن الكمية المطلوبة مش أكبر من الستوك المتاح في المخزن حالياً
                function ($attribute, $value, $fail) {
                    $variant = ProductVariant::find($this->product_variant_id);
                    if ($variant && $value > $variant->stock) {
                        $fail("عفواً، الكمية المطلوبة غير متوفرة في المخزن. المتاح حالياً هو: {$variant->stock} قطع فقط.");
                    }
                }
            ]
        ];
    }

    // لتوحيد شكل الـ Error Response لو الفالياديشن فشل
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'errors' => $validator->errors()
        ], 422));
    }
}
