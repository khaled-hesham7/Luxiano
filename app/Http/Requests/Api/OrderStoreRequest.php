<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class OrderStoreRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            // نتأكد إن العنوان اللي العميل اختاره موجود فعلاً في جدول العناوين ومملوك ليه
            'address_id'     => [
                'required',
                Rule::exists('addresses', 'id')->where('user_id', $this->user()->id)
            ],
            'payment_method' => 'required|in:COD,Card', // كاش أو فيزا
            'coupon_code'    => 'nullable|string|exists:coupons,code',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json(['errors' => $validator->errors()], 422));
    }
}
