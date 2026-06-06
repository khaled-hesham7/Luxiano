<?php

namespace App\Http\Requests\Api\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class UpdateCouponRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $couponId = $this->route('coupon') ?? $this->route('id');

        return [
            'code'            => [
                'sometimes',
                'required',
                'string',
                'max:50',
                Rule::unique('coupons', 'code')->ignore($couponId),
            ],
            'type'            => 'sometimes|required|string|in:percentage,fixed',
            'value'           => 'sometimes|required|numeric|min:0',
            'min_order_value' => 'nullable|numeric|min:0',
            'start_date'      => 'sometimes|required|date',
            'end_date'        => 'sometimes|required|date|after:start_date',
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
