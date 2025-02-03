<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

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
            'fullName' => 'required|min:2',
            'personal_id' => 'required|integer|regex:/^\d{9}$/',
        ];
    }

    public function messages()
    {
        return [
            'full_name.required' => 'שם המשתמש חסר',
            'full_name.min' => 'שם המשתמש צריך להיות לפחות שני תווים',
            'personal_id.required' => 'המספר האישי חסר',
            'personal_id.integer' => 'המספר האישי חסר',
            'personal_id.regex' => 'המספר האישי בפורמט לא נכון',

        ];
    }
}
