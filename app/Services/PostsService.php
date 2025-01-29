<?php

namespace App\Services;

use App\Enums\HttpStatusEnum;
use App\Http\Resources\PostResource;
use App\Models\Posts;
use Illuminate\Support\Facades\Log;

class PostsService
{
    public function getNews()
    {
        try {
            $posts = Posts::latest()->take(3)->get();
            // dd($latestNews[1]->Image()->get()->toArray());
            return PostResource::collection($posts);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return HttpStatusEnum::ERROR;
        }
    }
}
