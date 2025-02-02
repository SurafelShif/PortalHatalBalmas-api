<?php

namespace App\Services;

use App\Enums\HttpStatusEnum;
use App\Models\Category;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class CategoryService
{

    public function getCategories()
    {
        try {
            $categories = Category::withCount('posts')->get()->toArray();
            return $categories;
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return HttpStatusEnum::ERROR;
        }
    }
    public function deleteCategory($categoryId)
    {
        $category = Category::where('id', $categoryId)->first()->toArray();
        if (empty($category)) {
            return HttpStatusEnum::NOT_FOUND;
        }
        Category::destroy($categoryId);
        return Response::HTTP_OK;
    }
    public function updateCategory(int $categoryId, string $name)
    {
        $category = Category::where('id', $categoryId)->first();
        if (is_null($category)) {
            return HttpStatusEnum::NOT_FOUND;
        }
        $category->update([
            'name' => $name
        ]);
        return Response::HTTP_OK;
    }
    public function createCategory(string $name)
    {
        Category::create([
            "name" => $name
        ]);
        return Response::HTTP_OK;
    }
}
