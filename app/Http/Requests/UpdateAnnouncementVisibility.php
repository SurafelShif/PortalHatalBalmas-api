<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateAnnouncementVisibility extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'isVisible' => ['required', 'boolean']
        ];
    }

    public function messages(): array
    {
        return [
            'isVisible.required' => 'נראות ההכזרה הינה חובה',
            'isVisible.boolean' => 'נראות ההכזרה אינה בפורמט הנכון'
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
