<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;

class UpdateAnnouncementRequest extends FormRequest
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
            '*.uuid' => 'required| uuid',
            '*.title' => ['sometimes', 'string', 'max:255'],
            '*.description' => ['sometimes', 'string'],
            '*.content' => ['sometimes', 'json'],
            '*.position' => "sometimes| integer ",
            '*.isVisible' => ['sometimes', 'boolean'],
            '*.image' => ['sometimes', 'image', 'mimes:jpeg,png,jpg,jfif', 'max:2048'],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if (count($this->all()) === 0) {
                return $validator->errors()->add('', 'הכנס לפחות אתר אחד לעדכון');
            }
            foreach ($this->all() as $key => $item) {
                if (!isset($item['name']) && !isset($item['position']) && !isset($item['description']) && !isset($item['link']) && !array_key_exists('image', $item)) {
                    $validator->errors()->add("$key", 'הכנס לפחות ערך אחד לעדכון');
                }

                if (array_key_exists('position', $item)) {
                    $count = DB::table('announcements')->count();
                    if ($item['position'] > $count) {
                        $validator->errors()->add("$key", 'מיקום ההכרזה אינו יכול להיות גדול מכמות ההכרזות.');
                    }
                }
            }
        });
    }

    /**
     * Get custom error messages for validation rules.
     */
    public function messages(): array
    {
        return [
            '*.uuid.required' => 'נא לשלוח את מזהה האתר (UUID)',
            '*.uuid.uuid' => 'מזהה האתר אינו בפורמט הנכון',
            '*.title.string' => 'כותרת הכזרה חייבת להיות מחרוזת.',
            '*.title.max' => 'כותרת הכזרה יכולה להכיל עד 255 תווים.',
            '*.description.string' => 'תיאור הכזרה חייב להיות מחרוזת.',
            '*.content.json' => 'תוכן הכזרה חייב להיות בפורמט JSON תקין.',
            '*.position.unique' => 'המיקום שסיפקת כבר קיים במערכת.',
            '*.position.integer' => 'מיקום ההכזרה אינו בפורמט הנכון',
            '*.isVisible.boolean' => 'נראות ההכזרה אינו בפורמט הנכון',
            '*.image.image' => 'הקובץ חייב להיות תמונה.',
            '*.image.mimes' => 'התמונה חייבת להיות בפורמט: jpeg, png, jpg, jfif.',
            '*.image.max' => 'התמונה חייבת להיות עד 2MB.',
        ];
    }
}
