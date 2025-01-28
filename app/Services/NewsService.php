<?php

namespace App\Services;

use App\Enums\HttpStatusEnum;
use App\Models\News;
use Illuminate\Support\Facades\Log;

class NewsService
{
    public function getNews()
    {
        try {
            $latestNews = News::latest()->take(3)->get();
            return $latestNews->toArray();
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return HttpStatusEnum::ERROR;
        }
    }
}
