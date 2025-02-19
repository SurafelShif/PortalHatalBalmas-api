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
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;


class GlobalService
{

    public function search(?string $search, ?int $limit)
    {
        try {
            // Posts Query
            $postsQuery = Post::with('category')
                ->select([
                    'uuid',
                    'title',
                    'description',
                    'image_id',
                    'category_id',
                    DB::raw("NULL as link"),
                    DB::raw("'כתבה' as name"),
                    DB::raw("'post' as type"),
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
                    'image_id',
                    DB::raw("NULL as category_id"),
                    DB::raw("NULL as link"),
                    DB::raw("'הכרזה' as name"),
                    DB::raw("'announcement' as type")
                ])
                ->where(function ($q) use ($search) {
                    $q->where('title', 'LIKE', "%{$search}%")
                        ->orWhere('description', 'LIKE', "%{$search}%");
                });


            $informationQuery = Information::query()->select([
                'uuid',
                'title',
                DB::raw("NULL as description"),
                'image_id',
                DB::raw("NULL as category_id"),
                DB::raw("NULL as link"),
                DB::raw("'מידע' as name"),
                DB::raw("'info' as type"),
            ])
                ->where(function ($q) use ($search) {
                    $q->where('title', 'LIKE', "%{$search}%");
                });

            $sitesQuery = Site::query()->select([
                'uuid',
                DB::raw("name as title"),
                'description',
                'image_id',
                DB::raw("NULL as category_id"),
                'link',
                DB::raw("'קישור' as name"),
                DB::raw("'site' as type")
            ])
                ->where(function ($q) use ($search) {
                    $q->where('name', 'LIKE', "%{$search}%")
                        ->orWhere('description', 'LIKE', "%{$search}%");
                });

            $results = $postsQuery->union($announcementsQuery)
                ->union($informationQuery)
                ->union($sitesQuery)->limit($limit)
                ->get();
            return GlobalSearchResource::collection($results);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return HttpStatusEnum::ERROR;
        }
    }
}
