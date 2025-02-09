<?php

namespace App\Services;

use App\Enums\HttpStatusEnum;
use App\Http\Resources\InformationResource;
use App\Models\Information;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;

class InformationsService
{
    public function __construct(private ImageService $imageService) {}

    public function getInformations()
    {
        try {
            $informations = Information::all();
            return InformationResource::collection($informations);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return HttpStatusEnum::ERROR;
        }
    }
    public function createInformation(string $title, string $content, UploadedFile $image)
    {
        $createdImage = null;
        try {
            $createdImage = $this->imageService->uploadImage($image);
            Information::create([
                'title' => $title,
                'content' => json_decode($content, 1),
                'image_id' => $createdImage->id
            ]);
        } catch (\Exception $e) {
            $this->imageService->deleteImage($createdImage->image_name);
            Log::error($e->getMessage());
            return HttpStatusEnum::ERROR;
        }
    }
}
