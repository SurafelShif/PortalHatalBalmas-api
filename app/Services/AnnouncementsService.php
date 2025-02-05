<?php

namespace App\Services;

use App\Enums\HttpStatusEnum;
use App\Http\Resources\AnnoucementsResource;
use App\Models\Announcement;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;

class AnnouncementsService
{
    public function __construct(private ImageService $imageService) {}
    public function createAnnouncement(string $title, string $description, string $content, int $position, UploadedFile $image)
    {
        $createdImage = null;
        try {
            $createdImage = $this->imageService->uploadImage($image);
            Announcement::create([
                'title' => $title,
                'description' => $description,
                'content' => json_decode($content, 1),
                'position' => $position,
                'image_id' => $createdImage->id
            ]);
        } catch (\Exception $e) {
            $this->imageService->deleteImage($createdImage->image_name);
            Log::error($e->getMessage());
            return HttpStatusEnum::ERROR;
        }
    }
    public function getAnnouncements()
    {
        try {
            $annoucements = Announcement::all();
            return AnnoucementsResource::collection($annoucements);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return HttpStatusEnum::ERROR;
        }
    }
}
