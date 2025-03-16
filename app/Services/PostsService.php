<?php

namespace App\Services;

use App\Enums\HttpStatusEnum;
use App\Http\Resources\PostResource;
use App\Models\Post;
use Symfony\Component\HttpFoundation\Response;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class PostsService
{
    public function __construct(private ImageService $imageService) {}
    public function getPosts(int | null $category_id, int | null $limit, int $page, string| null $search)
    {
        try {
            $query = Post::with('category')->latest()->select(['image_id', 'title', 'uuid', "description", "category_id"]);
            if (!is_null($category_id)) {
                $query->whereHas('category', function ($q) use ($category_id) {
                    $q->where('filter_by', $category_id);
                });
            }
            if (!empty($search)) {
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'LIKE', "%{$search}%")
                        ->orWhere('description', 'LIKE', "%{$search}%")
                        ->orWhere('content', 'LIKE', "%{$search}%");
                });
            }
            $posts = is_null($limit) ? $query->get() : $query->paginate($limit, ['*'], 'page', $page);
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
            $post = Post::select(['image_id', 'title', 'uuid', 'category_id', 'content', 'created_at'])->where('uuid', $uuid)->first();
            if (is_null($post)) {
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
        $createdImage = null;
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
            $this->imageService->deleteImage($createdImage->image_name);
            Log::error($e->getMessage());
            return HttpStatusEnum::ERROR;
        }
    }
    public function deletePost(string $uuid)
    {
        try {
            $post = Post::where('uuid', $uuid)->first();
            if (is_null($post)) {
                return HttpStatusEnum::NOT_FOUND;
            }
            Post::destroy($post->id);
            return Response::HTTP_OK;
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return HttpStatusEnum::ERROR;
        }
    }
    public function updatePost(string $uuid, array $updateArray)
    {
        try {
            $post = Post::where('uuid', $uuid)->first();
            if (is_null($post)) {
                return HttpStatusEnum::NOT_FOUND;
            }
            if (array_key_exists('image', $updateArray)) {
                $this->imageService->updateImage($post->image->id, $updateArray['image']);
                unset($updateArray['image']);
                $post->refresh();
            }
            // if (array_key_exists('content', $updateArray)) {
            //     $updateArray['content'] = json_decode($updateArray['content'], 1);
            // }
            $post->update($updateArray);
            return new PostResource($post);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return HttpStatusEnum::ERROR;
        }
    }
}
