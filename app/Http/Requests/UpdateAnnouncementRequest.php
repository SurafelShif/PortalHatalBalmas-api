<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateAnnouncementRequest extends FormRequest
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
            'image' => ['sometimes', 'image', 'mimes:jpeg,png,jpg,jfif,webp', 'max:7168'],
        ];
    }

    /**
     * Get custom error messages for validation rules.
     */
    public function messages(): array
    {
        return [
            'title.string' => 'כותרת ההכרזה חייבת להיות מחרוזת.',
            'title.max' => 'כותרת ההכרזה יכולה להכיל עד 255 תווים.',
            'description.string' => 'תיאור ההכרזה חייב להיות מחרוזת.',
            'content.string' => 'תוכן הכתבה אינו בפורמט הנכון.',
            'image.image' => 'הקובץ חייב להיות תמונה.',
            'image.mimes' => 'התמונה חייבת להיות בפורמט: jpeg, png, jpg, jfif,webp.',
            'image.max' => 'התמונה חייבת להיות עד 7MB.',
        ];
    }

    /**
     * Apply custom validation logic.
     */

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if ($this->all() === []) {
                $validator->errors()->add('general', 'יש להזין לפחות שדה אחד לעדכון.');
            }
            $isEmptyContent = $this['content'] === "<p></p>";
            if ($isEmptyContent) {
                $validator->errors()->add("content", "תוכן ההכרזה הינו חובה");
            }
        });
    }
    /**
     * Handle failed validation response.
     */
    protected function failedValidation(Validator $validator)
    {
        $errors = collect($validator->errors()->messages())
            ->map(fn($messages) => $messages[0]);

        throw new HttpResponseException(response()->json([
            'errors' => $errors
        ], 422));
    }
}
