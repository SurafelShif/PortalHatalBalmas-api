<?php

namespace App\Http\Requests;

use App\Models\Category;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use SebastianBergmann\Type\NullType;
use Symfony\Component\HttpFoundation\Response;

class GetPostsRequest extends FormRequest
{
    public function authorize()
    {
        return true; // Set to false if only authorized users should use this request
    }

    public function rules()
    {
        return [
            'search' => 'nullable|string|max:100',
            'category_uuid' => 'nullable|uuid|exists:categories,uuid',
            'limit' => 'nullable|integer|min:1|max:10',
            'page' => 'nullable|integer|min:1',
        ];
    }
    public function messages()
    {
        return [
            'search.max' => 'השדה חיפוש לא יכול להכיל יותר מ-100 תווים.',
            'category_uuid.uuid' => 'מזהה קטגוריה חייב להיות בפורמט UUID.',
            'category_uuid.exists' => 'קטגוריה לא נמצאה.',
            'limit.integer' => 'הגבלת תוצאות חייבת להיות מספר.',
            'limit.min' => 'הגבלת תוצאות חייבת להיות לפחות 1.',
            'limit.max' => 'הגבלת תוצאות לא יכולה להיות יותר מ-10.',
            'page.integer' => 'עמוד חייב להיות מספר.',
            'page.min' => 'מספר העמוד חייב להיות לפחות 1.',
        ];
    }
    protected function failedValidation(Validator $validator)
    {
        $errors = collect($validator->errors()->messages())
            ->map(fn($messages) => $messages[0]); // Get only the first error message per field

        throw new HttpResponseException(response()->json([
            'errors' => $errors
        ], 422));
    }
}
