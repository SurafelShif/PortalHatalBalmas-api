<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
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
            'idToken' => 'required|string',
        ];
    }
    public function messages(): array
    {
        return [
            'idToken.required' => 'יש לספק מזהה לטוקן',
            'idToken.string' => 'פורמט אינו תקין',
        ];
    }
}
