<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class CreateInformationRequest extends FormRequest
{
    public function authorize()
    {
        return true; // Change to false if only authorized users should create posts
    }

    public function rules()
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
            'icon_name' => ['required', 'string'],
            'image' => ['required', 'image', 'mimes:jpeg,png,jpg,jfif', 'max:7168'],
        ];
    }


    public function messages()
    {
        return [
            'title.required' => 'כותרת הכתבה היא חובה.',
            'content.required' => 'תוכן הכתבה הוא חובה.',
            'content.string' => 'תוכן הכתבה אינו בפורמט הנכון.',
            'image.required' => 'חובה להעלות תמונה.',
            'image.image' => 'הקובץ חייב להיות תמונה.',
            'image.mimes' => 'התמונה חייבת להיות בפורמט: jpeg, png, jpg, jfif.',
            'image.max' => 'התמונה חייבת להיות עד 7MB.',
            'icon_name.required' => 'חובה לעלות אייקון',
            'icon_name.string' => 'אייקון אינו בפורמט הנכון'
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
