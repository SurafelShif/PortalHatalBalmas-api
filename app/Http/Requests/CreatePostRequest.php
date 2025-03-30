<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class CreatePostRequest extends FormRequest
{
    public function authorize()
    {
        return true; // Change to false if only authorized users should create posts
    }

    public function rules()
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'content' => ['required', 'string'],
            'category_uuid' => ['required', 'exists:categories,uuid'],
            'image' => ['required', 'image', 'mimes:jpeg,png,jpg,jfif', 'max:7168'],
        ];
    }

    public function messages()
    {
        return [
            'title.required' => 'כותרת הכתבה היא חובה.',
            'description.required' => 'תיאור הכתבה הוא חובה.',
            'content.required' => 'תוכן הכתבה הוא חובה.',
            'content.string' => 'תוכן הכתבה אינו בפורמט הנכון.',
            'category_uuid.required' => 'קטגוריה היא חובה.',
            'category_uuid.exists' => 'הקטגוריה שסיפקת אינה קיימת.',
            'image.required' => 'חובה להעלות תמונה.',
            'image.image' => 'הקובץ חייב להיות תמונה.',
            'image.mimes' => 'התמונה חייבת להיות בפורמט: jpeg, png, jpg, jfif.',
            'image.max' => 'התמונה חייבת להיות עד 7MB.',
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
