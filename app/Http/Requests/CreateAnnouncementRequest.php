<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class CreateAnnouncementRequest extends FormRequest
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
            'content' => ['required', 'json'],
            'image' => ['required', 'image', 'mimes:jpeg,png,jpg,jfif', 'max:2048'],
        ];
    }


    public function messages()
    {

        return [
            'title.required' => 'כותרת הכזרה היא חובה.',
            'description.required' => 'תיאור הכזרה הוא חובה.',
            'content.required' => 'תוכן הכזרה הוא חובה.',
            'content.json' => 'תוכן הכזרה אינה בפורמט הנכון.',
            'position.required' => 'מיקום ההכזרה היא חובה.',
            'position.integer' => 'מיקום ההכזרה אינה בפורמט הנכון.',
            'image.required' => 'חובה להעלות תמונה.',
            'image.image' => 'הקובץ חייב להיות תמונה.',
            'image.mimes' => 'התמונה חייבת להיות בפורמט: jpeg, png, jpg, jfif.',
            'image.max' => 'התמונה חייבת להיות עד 2MB.',
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
