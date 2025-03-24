<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateSiteRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'description' => ['sometimes', 'string'],
            'link' => ['sometimes', 'url'],
            'icon_name' => ['sometimes', 'string'],
        ];
    }

    public function messages()
    {
        return [
            'name.string' => 'שם האפליקציה אינו בפורמט',
            'link.url' => 'הלינק אינו בפוורמט הנכון.',
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
