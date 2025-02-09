<?php

namespace App\Services;

use App\Enums\HttpStatusEnum;
use App\Http\Resources\SitesResource;
use App\Models\Site;
use Symfony\Component\HttpFoundation\Response;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;

class SitesService
{
    public function __construct(private ImageService $imageService) {}

    public function getSites()
    {
        try {
            $sites = Site::all();
            return SitesResource::collection($sites);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return HttpStatusEnum::ERROR;
        }
    }

    public function createSite(string $name, string $description, string $link, UploadedFile $image)
    {
        $createdImage = null;
        try {
            $createdImage = $this->imageService->uploadImage($image);
            Site::create([
                'name' => $name,
                'description' => $description,
                'link' => $link,
                'image_id' => $createdImage->id
            ]);
        } catch (\Exception $e) {
            $this->imageService->deleteImage($createdImage->image_name);
            Log::error($e->getMessage());
            return HttpStatusEnum::ERROR;
        }
    }
    public function deleteSite($uuid)
    {
        try {
            $site = Site::where('uuid', $uuid)->first();
            if (is_null($site)) {
                return HttpStatusEnum::NOT_FOUND;
            }
            Site::destroy($site->id);
            return Response::HTTP_OK;
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return HttpStatusEnum::ERROR;
        }
    }

    public function updateSite(string $uuid, array $updateArray)
    {
        try {
            $post = Site::where('uuid', $uuid)->first();
            if (is_null($post)) {
                return HttpStatusEnum::NOT_FOUND;
            }
            if (array_key_exists('image', $updateArray)) {
                $this->imageService->updateImage($post->image->id, $updateArray['image']);
                unset($updateArray['image']);
            }
            $post->update($updateArray);
            return Response::HTTP_OK;
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return HttpStatusEnum::ERROR;
        }
    }
}
