<?php

namespace App\Services;

use App\Enums\HttpStatusEnum;
use App\Http\Resources\InformationResource;
use App\Models\Information;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class InformationsService
{
    public function __construct(private ImageService $imageService) {}

    public function getInformations()
    {
        try {
            $informations = Information::select(['uuid', 'title', 'icon_name', 'content', 'preview_image_id'])->orderBy('created_at', 'desc')->get();
            return InformationResource::collection($informations);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return HttpStatusEnum::ERROR;
        }
    }
    public function createInformation(string $title, string $content, UploadedFile $image, string $icon_name)
    {
        try {
            $image = $this->imageService->uploadImage($image);
            Information::create([
                'title' => $title,
                'content' => $content,
                'icon_name' => $icon_name,
                'preview_image_id' => $image->id
            ]);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return HttpStatusEnum::ERROR;
        }
    }
    public function deleteInformation($uuid)
    {
        try {
            $information = Information::where('uuid', $uuid)->first();
            if (is_null($information)) {
                return HttpStatusEnum::NOT_FOUND;
            }
            Information::destroy($information->id);
            return Response::HTTP_OK;
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return HttpStatusEnum::ERROR;
        }
    }
    public function updateInformation(string $uuid, array $updateArray)
    {
        try {
            $information = Information::where('uuid', $uuid)->first();
            if (is_null($information)) {
                return HttpStatusEnum::NOT_FOUND;
            }
            if (array_key_exists('image', $updateArray)) {
                $this->imageService->updateImage($information->image->id, $updateArray['image']);
                unset($updateArray['image']);
                $information->refresh();
            }
            $information->update($updateArray);
            return new InformationResource($information);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return HttpStatusEnum::ERROR;
        }
    }
    public function getInformationByUUid($uuid)
    {
        try {
            // if (!Str::isUuid($uuid)) {
            //     return HttpStatusEnum::BAD_REQUEST;
            // }
            $post = Information::where('uuid', $uuid)->first();
            if (is_null($post)) {
                return HttpStatusEnum::NOT_FOUND;
            }
            return new InformationResource($post);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return HttpStatusEnum::ERROR;
        }
    }
}
