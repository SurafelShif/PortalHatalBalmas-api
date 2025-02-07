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
            'title' => ['sometimes', 'string', 'max:255'],
            'description' => ['sometimes', 'string'],
            'content' => ['sometimes', 'json'],
            'position' => "sometimes| integer ",
            'isVisible' => ['sometimes', 'boolean'],
            'image' => ['sometimes', 'image', 'mimes:jpeg,png,jpg,jfif', 'max:2048'],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if (!$this->hasAny(['title', 'description', 'content', 'position', 'isVisible', 'image', 'category_id'])) {
                $validator->errors()->add('general', 'חייב לשלוח לפחות שדה אחד לעדכון.');
            }
            if ($this->has('position')) {
                $count = DB::table('announcements')->count();
                if ($this->position > $count) {
                    $validator->errors()->add('position', 'מיקום ההכרזה אינו יכול להיות גדול מכמות ההכרזות.');
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
            'title.string' => 'כותרת הכזרה חייבת להיות מחרוזת.',
            'title.max' => 'כותרת הכזרה יכולה להכיל עד 255 תווים.',

            'description.string' => 'תיאור הכזרה חייב להיות מחרוזת.',

            'content.json' => 'תוכן הכזרה חייב להיות בפורמט JSON תקין.',

            'position.unique' => 'המיקום שסיפקת כבר קיים במערכת.',

            'position.integer' => 'מיקום ההכזרה אינו בפורמט הנכון',

            'isVisible.boolean' => 'נראות ההכזרה אינו בפורמט הנכון',

            'image.image' => 'הקובץ חייב להיות תמונה.',
            'image.mimes' => 'התמונה חייבת להיות בפורמט: jpeg, png, jpg, jfif.',
            'image.max' => 'התמונה חייבת להיות עד 2MB.',
        ];
    }
}
