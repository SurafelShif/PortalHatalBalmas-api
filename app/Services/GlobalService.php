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
            $posts = Post::with('category')
                ->selectRaw("
        id, uuid, title, description, category_id,
        NULL as link, 'כתבה' as name, 'post' as type, created_at
    ")
                ->when($search, function ($q) use ($search) {
                    $q->where('title', 'LIKE', "%{$search}%")
                        ->orWhere('description', 'LIKE', "%{$search}%");
                })
                ->get();

            $announcements = Announcement::with('previewImage')
                ->selectRaw("
        id, uuid, title, description, NULL as category_id,
        NULL as link, 'הכרזה' as name, 'announcement' as type, created_at
    ")
                ->when($search, function ($q) use ($search) {
                    $q->where('title', 'LIKE', "%{$search}%")
                        ->orWhere('description', 'LIKE', "%{$search}%");
                })
                ->get();

            $information = Information::selectRaw("
        id, uuid, title, NULL as description, NULL as category_id,
        NULL as link, 'מידע' as name, 'info' as type, created_at
    ")
                ->when($search, function ($q) use ($search) {
                    $q->where('title', 'LIKE', "%{$search}%");
                })
                ->get();

            $sites = Site::selectRaw("
        id, uuid, name as title, description, NULL as category_id,
        link, 'קישור' as name, 'site' as type, created_at
    ")
                ->when($search, function ($q) use ($search) {
                    $q->where('name', 'LIKE', "%{$search}%")
                        ->orWhere('description', 'LIKE', "%{$search}%");
                })
                ->get();

            $results = $posts->concat($announcements)->concat($information)->concat($sites);
            $results = $results->sortByDesc('created_at')->take($limit);

            return GlobalSearchResource::collection($results);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return HttpStatusEnum::ERROR;
        }
    }


    public function commitContentImages(Model $model, string $content)
    {
        try {
            $matches = $this->getImageNamesFromContent($content);


            foreach ($matches as $src) {
                $imageName = basename(parse_url($src, PHP_URL_PATH));
                if (!Storage::disk(config('filesystems.storage_service'))->exists('images/' . $imageName))
                    continue;
                $image = Image::where("image_name", $imageName)->first();

                if (!$image) {
                    continue;
                }
                $image->is_commited = true;
                $image->imageable()->associate($model);
                $image->save();
            }
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return HttpStatusEnum::ERROR;
        }
    }
    public function removeCommitedContentImages(Model $model, string $content)
    {
        $sources = $this->getImageNamesFromContent($content);
        $existingImages = $model->images()->where('is_commited', true)->get();
        foreach ($existingImages as $image) {
            if (!in_array($image->image_name, $sources)) {
                $image->delete();
            }
        }
    }
    private function getImageNamesFromContent(string $content)
    {
        preg_match_all('/<img[^>]+src="[^">]+\/([^\/">]+)"/i', $content, $matches);
        return  $matches[1];
    }
}
