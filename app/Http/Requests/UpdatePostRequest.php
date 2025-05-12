<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

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
            'content' => ['sometimes', 'string'],
            'category_uuid' => ['sometimes', 'exists:categories,uuid'],
            'image' => ['sometimes', 'image', 'mimes:jpeg,png,jpg,jfif', 'max:7168'],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if (empty($this->all())) {
                $validator->errors()->add('general', 'חייב לשלוח לפחות שדה אחד לעדכון.');
            }
            $isEmptyContent = $this['content'] === "<p></p>";
            if ($isEmptyContent) {
                $validator->errors()->add("content", "תוכן הכתבה הינו חובה");
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

            'content.string' => 'תוכן הכתבה אינו בפורמט הנכון.',

            'category_uuid.exists' => 'הקטגוריה שסיפקת אינה קיימת.',

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
