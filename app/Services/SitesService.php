<?php

namespace App\Services;

use App\Enums\HttpStatusEnum;
use App\Http\Resources\SitesResource;
use App\Models\Site;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Str;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;

class SitesService
{
    public function __construct(private ImageService $imageService) {}

    public function getSites(string | null $searcQuery)
    {
        try {
            $query = Site::latest();
            if (!empty($searcQuery)) {
                $query->where(function ($q) use ($searcQuery) {
                    $q->where('name', 'LIKE', "%{$searcQuery}%")
                        ->orWhere('description', 'LIKE', "%{$searcQuery}%");
                });
            }
            $sites = $query->get();
            return SitesResource::collection($sites);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return HttpStatusEnum::ERROR;
        }
    }

    public function createSite(string $name, string $description, string $link, UploadedFile $image)
    {

        try {
            $image = $this->imageService->storeImage($image);
            $model = Site::create([
                'name' => $name,
                'description' => $description,
                'link' => $link,
            ]);
            $this->imageService->saveImage($model, $image, "preview");
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            $this->imageService->deleteImage($image['randomFileName']);
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
            $site = Site::where('uuid', $uuid)->first();
            if (is_null($site)) {
                return HttpStatusEnum::NOT_FOUND;
            }
            if (array_key_exists('image', $updateArray)) {
                $this->imageService->updateImage($site->previewImage->id, $updateArray['image']);
                unset($updateArray['image']);
                $site->refresh();
            }
            $site->update($updateArray);
            return new SitesResource($site);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return HttpStatusEnum::ERROR;
        }
    }
}
