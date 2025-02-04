<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePostRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'string', 'max:255'],
            'description' => ['sometimes', 'string'],
            'content' => ['sometimes', 'json'],
            'category_id' => ['sometimes', 'exists:categories,id'],
            'image' => ['sometimes', 'image', 'mimes:jpeg,png,jpg,jfif', 'max:2048'],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if (empty($this->all())) {
                $validator->errors()->add('general', 'חייב לשלוח לפחות שדה אחד לעדכון.');
            }
        });
    }

    /**
     * Get custom error messages for validation rules.
     */
    public function messages(): array
    {
        return [
            'title.string' => 'כותרת הכתבה חייבת להיות מחרוזת.',
            'title.max' => 'כותרת הכתבה יכולה להכיל עד 255 תווים.',

            'description.string' => 'תיאור הכתבה חייב להיות מחרוזת.',

            'content.json' => 'תוכן הכתבה חייב להיות בפורמט JSON תקין.',

            'category_id.exists' => 'הקטגוריה שסיפקת אינה קיימת.',

            'image.image' => 'הקובץ חייב להיות תמונה.',
            'image.mimes' => 'התמונה חייבת להיות בפורמט: jpeg, png, jpg, jfif.',
            'image.max' => 'התמונה חייבת להיות עד 2MB.',
        ];
    }
}
