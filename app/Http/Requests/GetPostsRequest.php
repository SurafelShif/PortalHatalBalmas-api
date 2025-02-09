<?php

namespace App\Http\Requests;

use App\Models\Category;
use Illuminate\Foundation\Http\FormRequest;
use SebastianBergmann\Type\NullType;
use Symfony\Component\HttpFoundation\Response;

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
            'category_id' => 'nullable|integer',
            'limit' => 'nullable|integer|min:1|max:10',
            'page' => 'nullable|integer|min:1',
        ];
    }
    protected function passedValidation()
    {
        if ($this->filled('category_id')) {
            $exists = Category::where('id', $this->category_id)->exists();
            if (!$exists) {
                abort(response()->json("", Response::HTTP_NOT_FOUND));
            }
        }
    }
    public function messages()
    {
        return [
            'search.max' => 'השדה חיפוש לא יכול להכיל יותר מ-100 תווים.',
            'category_id.integer' => 'קטגוריה חייבת להיות מספר.',
            'limit.integer' => 'הגבלת תוצאות חייבת להיות מספר.',
            'limit.min' => 'הגבלת תוצאות חייבת להיות לפחות 1.',
            'limit.max' => 'הגבלת תוצאות לא יכולה להיות יותר מ-10.',
            'page.integer' => 'עמוד חייב להיות מספר.',
            'page.min' => 'מספר העמוד חייב להיות לפחות 1.',
        ];
    }
}
