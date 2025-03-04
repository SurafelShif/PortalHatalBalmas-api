<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class CategoryRequest extends FormRequest
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
            "name" => "required|string| unique:categories,name"
        ];
    }


    public function messages(): array
    {
        return [
            "name.required" => "שם הקטגוריה הינו חובה",
            "name.unique" => "שם הקטגוריה קיימת",
            "name.string" => "שם הקטגוריה אינו בפורמט הנכון"
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
