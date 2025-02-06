<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

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
}
