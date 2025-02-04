<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

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
            'content' => ['required', 'json'],
            'category_id' => ['required', 'exists:categories,id'],
            'image' => ['required', 'image', 'mimes:jpeg,png,jpg,jfif', 'max:2048'],
        ];
    }

    public function messages()
    {
        return [
            'title.required' => 'כותרת הכתבה היא חובה.',
            'description.required' => 'תיאור הכתבה הוא חובה.',
            'content.required' => 'תוכן הכתבה הוא חובה.',
            'content.json' => 'תוכן הכתבה אינה בפורמט הנכון.',
            'category_id.required' => 'קטגוריה היא חובה.',
            'category_id.exists' => 'הקטגוריה שסיפקת אינה קיימת.',
            'image.required' => 'חובה להעלות תמונה.',
            'image.image' => 'הקובץ חייב להיות תמונה.',
            'image.mimes' => 'התמונה חייבת להיות בפורמט: jpeg, png, jpg, jfif.',
            'image.max' => 'התמונה חייבת להיות עד 2MB.',
        ];
    }
}
