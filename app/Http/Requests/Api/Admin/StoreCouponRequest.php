<?php

namespace App\Http\Requests\Api\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreCouponRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'code'            => 'required|string|max:50|unique:coupons,code',
            'type'            => 'required|string|in:percentage,fixed',
            'value'           => 'required|numeric|min:0',
            'min_order_value' => 'nullable|numeric|min:0',
            'start_date'      => 'required|date',
            'end_date'        => 'required|date|after:start_date',
            'usage_limit'     => 'nullable|integer|min:1',
            'is_active'       => 'boolean',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'errors' => $validator->errors()
        ], 422));
    }
}
