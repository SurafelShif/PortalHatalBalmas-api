<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class CreateSiteRequest extends FormRequest
{
    public function authorize()
    {
        return true; // Change to false if only authorized users should create posts
    }

    public function rules()
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'link' => ['required', 'url'],
            'icon_name' =>  ['required', 'string'],
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'שם האתר היא חובה.',
            'link.required' => 'לינק האתר היא חובה.',
            'link.url' => 'הלינק אינו בפוורמט הנכון.',
            'description.required' => 'תיאור האתר הוא חובה.',
            'icon_name.required' => 'שם האייקון הינו חובה',
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
