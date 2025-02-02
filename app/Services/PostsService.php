<?php

namespace App\Services;

use App\Enums\HttpStatusEnum;
use App\Http\Resources\PostResource;
use App\Models\Post;
use Illuminate\Support\Facades\Log;


class PostsService
{
    public function getPost(string | null $category, int $perPage, int $page, string| null $search)
    {
        try {
            $query = Post::latest()->select(['image_id', 'title', 'description', 'uuid']);
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
            $Post = $query->paginate(
                $perPage,
                ['*'],
                'page',
                $page
            );
            return PostResource::collection($Post);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return HttpStatusEnum::ERROR;
        }
    }

    public function getPostByUUid(string $uuid)
    {
        try {
            // if (!Str::isUuid($uuid)) {
            //     return HttpStatusEnum::BAD_REQUEST;
            // }
            $post = Post::where('uuid', $uuid)->first();
            if (!$post) {
                return HttpStatusEnum::NOT_FOUND;
            }
            return new PostResource($post);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return HttpStatusEnum::ERROR;
        }
    }
}
