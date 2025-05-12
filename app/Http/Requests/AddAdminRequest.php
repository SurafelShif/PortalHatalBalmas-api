<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class AddAdminRequest extends FormRequest
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
            'full_name' => 'required|min:2',
            'personal_id' => 'required|integer|regex:/^\d{9}$/',
        ];
    }

    public function messages()
    {
        return [
            'full_name.required' => 'שם המשתמש חסר',
            'full_name.min' => 'שם המשתמש צריך להיות לפחות שני תווים',
            'personal_id.required' => 'תעודת הזהות חסרה',
            'personal_id.integer' => 'תעודת הזהות אינה בפורמט הנכון',
            'personal_id.regex' => 'תעודת הזהות צריכה להיות 9 תווים',

        ];
    }
    protected function failedValidation(Validator $validator)
    {
        $errors = collect($validator->errors()->messages())
            ->map(fn($messages) => $messages[0]);

        throw new HttpResponseException(response()->json([
            'errors' => $errors
        ], 422));
    }
}
