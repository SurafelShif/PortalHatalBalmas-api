<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateInformationRequest extends FormRequest
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
            'content' => ['sometimes', 'string'],
            'icon_name' => ['sometimes', 'string'],
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
            'content.string' => 'תוכן הכתבה אינו בפורמט הנכון.',
            'icon_name.string' => 'שם האייקון אינו בפורמט הנכון'
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
