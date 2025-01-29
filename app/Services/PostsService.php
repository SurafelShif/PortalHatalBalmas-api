<?php

namespace App\Services;

use App\Enums\HttpStatusEnum;
use App\Models\Posts;
use Illuminate\Support\Facades\Log;

class PostsService
{
    public function getNews()
    {
        try {
            $latestNews = Posts::latest()->take(3)->get();
            return $latestNews->toArray();
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return HttpStatusEnum::ERROR;
        }
    }
}
