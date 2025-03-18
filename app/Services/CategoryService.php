<?php

namespace App\Services;

use App\Enums\HttpStatusEnum;
use App\Models\Category;
use Symfony\Component\HttpFoundation\Response;

use Illuminate\Support\Facades\Log;

class CategoryService
{

    public function getCategories()
    {
        try {
            $categories = Category::withCount('posts')->orderBy('created_at', 'desc')->get()->toArray();
            return $categories;
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return HttpStatusEnum::ERROR;
        }
    }
    public function deleteCategory($category_uuid)
    {
        try {
            $category = Category::where('uuid', $category_uuid)->first();
            if (empty($category)) {
                return HttpStatusEnum::NOT_FOUND;
            }
            Category::destroy($category->id);
            return Response::HTTP_OK;
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return HttpStatusEnum::ERROR;
        }
    }
    public function updateCategory(string $category_uuid, string $name)
    {
        try {
            $category = Category::where('uuid', $category_uuid)->first();
            if (is_null($category)) {
                return HttpStatusEnum::NOT_FOUND;
            }
            $category->update([
                'name' => $name
            ]);
            return $category;
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return HttpStatusEnum::ERROR;
        }
    }
    public function createCategory(string $name)
    {
        try {
            $category = Category::create([
                "name" => $name
            ]);
            return $category;
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return HttpStatusEnum::ERROR;
        }
    }
}
