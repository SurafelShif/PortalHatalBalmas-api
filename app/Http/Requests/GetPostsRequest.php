<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GetPostsRequest extends FormRequest
{
    public function authorize()
    {
        return true; // Set to false if only authorized users should use this request
    }

    public function rules()
    {
        return [
            'search' => 'nullable|string|max:100',
            'category_id' => 'nullable|integer|exists:categories,id',
            'limit' => 'nullable|integer|min:1|max:10',
            'page' => 'nullable|integer|min:1',
        ];
    }

    public function messages()
    {
        return [
            'search.max' => 'השדה חיפוש לא יכול להכיל יותר מ-100 תווים.',
            'category_id.integer' => 'קטגוריה חייבת להיות מספר.',
            'category_id.exists' => 'הקטגוריה שנבחרה לא קיימת.',
            'limit.integer' => 'הגבלת תוצאות חייבת להיות מספר.',
            'limit.min' => 'הגבלת תוצאות חייבת להיות לפחות 1.',
            'limit.max' => 'הגבלת תוצאות לא יכולה להיות יותר מ-10.',
            'page.integer' => 'עמוד חייב להיות מספר.',
            'page.min' => 'מספר העמוד חייב להיות לפחות 1.',
        ];
    }
}
