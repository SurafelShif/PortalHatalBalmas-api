<?php

namespace App\Services;

use App\Enums\HttpStatusEnum;
use App\Http\Resources\GlobalSearchResource;
use App\Http\Resources\PostResource;
use App\Models\Announcement;
use App\Models\Image;
use App\Models\Information;
use App\Models\Post;
use App\Models\Site;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;


class GlobalService
{
    public function __construct(private ImageService $imageService) {}
    public function search(?string $search, ?int $limit)
    {
        try {
            // Posts Query
            $postsQuery = Post::with('category')
                ->select([
                    'uuid',
                    'title',
                    'description',
                    'preview_image_id',
                    'category_id',
                    DB::raw("NULL as link"),
                    DB::raw("'כתבה' as name"),
                    DB::raw("'post' as type"),
                    'created_at',
                ])
                ->where(function ($q) use ($search) {
                    $q->where('title', 'LIKE', "%{$search}%")
                        ->orWhere('description', 'LIKE', "%{$search}%");
                });
            $announcementsQuery = Announcement::query()
                ->select([
                    'uuid',
                    'title',
                    'description',
                    'preview_image_id',
                    DB::raw("NULL as category_id"),
                    DB::raw("NULL as link"),
                    DB::raw("'הכרזה' as name"),
                    DB::raw("'announcement' as type"),
                    'created_at',
                ])
                ->where(function ($q) use ($search) {
                    $q->where('title', 'LIKE', "%{$search}%")
                        ->orWhere('description', 'LIKE', "%{$search}%");
                });


            $informationQuery = Information::query()->select([
                'uuid',
                'title',
                DB::raw("NULL as description"),
                'preview_image_id',
                DB::raw("NULL as category_id"),
                DB::raw("NULL as link"),
                DB::raw("'מידע' as name"),
                DB::raw("'info' as type"),
                'created_at',
            ])
                ->where(function ($q) use ($search) {
                    $q->where('title', 'LIKE', "%{$search}%");
                });

            $sitesQuery = Site::query()->select([
                'uuid',
                DB::raw("name as title"),
                'description',
                'preview_image_id',
                DB::raw("NULL as category_id"),
                'link',
                DB::raw("'קישור' as name"),
                DB::raw("'site' as type"),
                'created_at',
            ])
                ->where(function ($q) use ($search) {
                    $q->where('name', 'LIKE', "%{$search}%")
                        ->orWhere('description', 'LIKE', "%{$search}%");
                });

            $results = $postsQuery->union($announcementsQuery)
                ->union($informationQuery)
                ->union($sitesQuery)->limit($limit)->orderBy('created_at', 'desc')
                ->get();
            return GlobalSearchResource::collection($results);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return HttpStatusEnum::ERROR;
        }
    }
    public function createImagesFromBase64(string $content, Model $model)
    {
        $created_images = [];
        try {
            preg_match_all('/data:image\/(.*?);base64,(.*?)"/', $content, $matches);
            $images = $matches[2];
            $types = $matches[1];
            foreach ($images as $index => $image) {
                $created_image = $this->imageService->uploadStringImage(base64_decode($image), $types[$index], $model);
                $created_images[] = $created_image->image_name;
            }
            return $created_images;
        } catch (\Exception $e) {
            foreach ($created_images as $image) {
                $this->imageService->deleteImage($image);
            }
            Log::error($e->getMessage());
            return HttpStatusEnum::ERROR;
        }
    }
    public function updateContent($content, Model $model)
    {
        preg_match_all('/data:image\/(.*?);base64,(.*?)"/', $content, $matches);
        if (!empty($matches[2])) {
            $createdImages = $this->createImagesFromBase64($content, $model);
            foreach ($matches[2] as $index => $base64Data) {
                $oldString = "data:image/{$matches[1][$index]};base64,{$base64Data}\"";
                $newString = config('filesystems.storage_path') . 'images/' . $createdImages[$index] . '"';

                $content = str_replace($oldString, $newString, $content);
            }
        }
        return $content;
    }
}
