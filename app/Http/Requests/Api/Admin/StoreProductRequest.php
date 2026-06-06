<?php

namespace App\Http\Requests\Api\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'category_id' => 'required|exists:categories,id',
            'name'        => 'required|string|max:255',
            'slug'        => 'required|string|max:255|unique:products,slug',
            'description' => 'nullable|string',
            'base_price'  => 'required|numeric|min:0',
            'status'      => 'nullable|string|in:active,draft,inactive',
            // ميديا للرفع المتعدد للصور وفيديو واحد اختياري
            'images'      => 'nullable|array',
            'images.*'    => 'image|mimes:jpeg,png,jpg,webp|max:5120', // بحد أقصى 5 ميجابايت للصورة
            'video'       => 'nullable|file|mimes:mp4,mov,avi,webm|max:20480', // بحد أقصى 20 ميجابايت للفيديو
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'errors' => $validator->errors()
        ], 422));
    }
}
