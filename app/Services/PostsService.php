<?php

namespace App\Services;

use App\Enums\HttpStatusEnum;
use App\Http\Resources\PostResource;
use App\Models\Post;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;


class PostsService
{
    public function __construct(private ImageService $imageService) {}
    public function getPosts(string | null $category, int $perPage, int $page, string| null $search)
    {
        try {
            $query = Post::with('category')->latest()->select(['image_id', 'title', 'description', 'uuid', 'category_id']);
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
    public function createPosts(string $title, string $description, string $content, int $category_id, UploadedFile $image)
    {
        try {
            $createdImage = $this->imageService->uploadImage($image);
            Post::create([
                'title' => $title,
                'description' => $description,
                'content' => $content,
                'category_id' => $category_id,
                'image_id' => $createdImage->id
            ]);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return HttpStatusEnum::ERROR;
        }
    }
}
