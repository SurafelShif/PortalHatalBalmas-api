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
            $informations = Information::select(['uuid', 'title', 'image_id', 'content'])->get();
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
            }
            if (array_key_exists('content', $updateArray)) {
                $updateArray['content'] = json_decode($updateArray['content'], 1);
            }
            $information->update($updateArray);
            return Response::HTTP_OK;
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
