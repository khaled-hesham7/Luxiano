<?php

namespace App\Http\Requests\Api\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreVariantRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'sku'                   => 'required|string|max:255|unique:product_variants,sku',
            'price'                 => 'nullable|numeric|min:0',
            'stock'                 => 'required|integer|min:0',
            'attribute_value_ids'   => 'required|array|min:1',
            'attribute_value_ids.*' => 'integer|exists:attribute_values,id',
            'images'                => 'nullable|array',
            'images.*'              => 'image|mimes:jpeg,png,jpg,webp|max:5120',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'errors' => $validator->errors()
        ], 422));
    }
}
