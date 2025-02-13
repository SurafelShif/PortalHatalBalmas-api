<?php

namespace App\Services;

use App\Enums\HttpStatusEnum;
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

    public function search(?string $search)
    {
        try {
            // Posts Query
            $postsQuery = Post::select([
                'uuid',
                'title',
                'description',
                'image_id',
                'content',
                DB::raw("NULL as link"), // Placeholder for 'link' column from Sites
                DB::raw("'פוסט' as type")
            ])
                ->where(function ($q) use ($search) {
                    $q->where('title', 'LIKE', "%{$search}%")
                        ->orWhere('description', 'LIKE', "%{$search}%")
                        ->orWhere('content', 'LIKE', "%{$search}%");
                });

            // Announcements Query
            $announcementsQuery = Announcement::select([
                'uuid',
                'title',
                'description',
                'image_id',
                'content',
                DB::raw("NULL as link"), // Placeholder for 'link' column from Sites
                DB::raw("'הכרזה' as type")
            ])
                ->where(function ($q) use ($search) {
                    $q->where('title', 'LIKE', "%{$search}%")
                        ->orWhere('description', 'LIKE', "%{$search}%")
                        ->orWhere('content', 'LIKE', "%{$search}%");
                });

            // Information Query (Missing 'description' so we add NULL)
            $informationQuery = Information::select([
                'uuid',
                'title',
                DB::raw("NULL as description"), // Information does not have description
                'image_id',
                'content',
                DB::raw("NULL as link"), // Placeholder for 'link' column from Sites
                DB::raw("'כתבת מידע' as type")
            ])
                ->where(function ($q) use ($search) {
                    $q->where('title', 'LIKE', "%{$search}%")
                        ->orWhere('content', 'LIKE', "%{$search}%");
                });

            // Sites Query (Renaming 'name' to 'title' and adding placeholders)
            $sitesQuery = Site::select([
                'uuid',
                DB::raw("name as title"), // Rename 'name' to match other tables
                'description',
                'image_id',
                DB::raw("NULL as content"), // Placeholder for 'content' column from Posts/Announcements
                'link', // Sites has 'link', other tables do not
                DB::raw("'לינק' as type")
            ])
                ->where(function ($q) use ($search) {
                    $q->where('name', 'LIKE', "%{$search}%")
                        ->orWhere('description', 'LIKE', "%{$search}%")
                        ->orWhere('link', 'LIKE', "%{$search}%");
                });

            // Combine all queries with UNION
            $results = $postsQuery->union($announcementsQuery)
                ->union($informationQuery)
                ->union($sitesQuery)
                ->get();

            return $results;
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return HttpStatusEnum::ERROR;
        }
    }
}
