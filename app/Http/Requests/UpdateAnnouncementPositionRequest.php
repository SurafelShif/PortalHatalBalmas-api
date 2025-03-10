<?php

namespace App\Http\Requests;

use App\Models\Announcement;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class UpdateAnnouncementPositionRequest extends FormRequest
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
            "*.uuid" => "required|uuid|exists:announcements,uuid",
            "*.position" => "required|integer|min:0"
        ];
    }


    public function withValidator(Validator $validator)
    {
        $validator->after(function ($validator) {
            if (count($this->all()) === 0) {
                $validator->errors()->add(
                    "general",
                    "לפחות הכרזה אחת לעדכון"
                );
            }
            $announcementsCount = Announcement::count();
            foreach ($this->all() as $index => $item) {

                if ($item['position'] > $announcementsCount) {
                    $validator->errors()->add(
                        "$index.position",
                        "מיקום ההכרזה אינו תקין"
                    );
                }
            }
        });
    }

    /**
     * Custom error messages.
     */
    public function messages(): array
    {
        return [
            "*.uuid.required" => "מזהה האתר הינו חובה",
            "*.uuid.uuid" => "פורמט מזהה ההכרזה אינו תקין",
            "*.uuid.exists" => "ההכרזה אינה קיימת",
            "*.position.required" => "מיקום ההכרזה הינו חובה",
            "*.position.integer" => "המיקום חייב להיות מספר שלם",
            "*.position.min" => "המיקום חייב להיות לפחות 0",
        ];
    }
}
