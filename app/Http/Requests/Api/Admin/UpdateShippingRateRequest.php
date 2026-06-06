<?php

namespace App\Http\Requests\Api\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class UpdateShippingRateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rateId = $this->route('shipping_rate') ?? $this->route('id');

        return [
            'governorate' => [
                'sometimes',
                'required',
                'string',
                'max:100',
                Rule::unique('shipping_rates', 'governorate')->ignore($rateId),
            ],
            'cost'        => 'sometimes|required|numeric|min:0',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'errors' => $validator->errors()
        ], 422));
    }
}
