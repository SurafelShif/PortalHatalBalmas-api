<?php

namespace App\Services;

use App\Enums\HttpStatusEnum;
use App\Http\Resources\PostResource;
use App\Models\Posts;
use Illuminate\Support\Facades\Log;

class PostsService
{
    public function getPosts(string | null $category, int $perPage, int $page, string| null $search)
    {
        try {
            $query = Posts::latest();
            if (!empty($category)) {
                $query->whereHas('category', function ($q) use ($category) {
                    $q->where('name', $category);
                });
            }
            if (!empty($search)) {
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'LIKE', "%{$search}%")
                        ->orWhere('description', 'LIKE', "%{$search}%")
                        ->orWhere('content', 'LIKE', "%{$search}%");
                });
            }
            $posts = $query->paginate(
                $perPage,
                ['*'],
                'page',
                $page
            );
            return PostResource::collection($posts);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return HttpStatusEnum::ERROR;
        }
    }
}
