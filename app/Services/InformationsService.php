<?php

namespace App\Services;

use App\Enums\HttpStatusEnum;
use App\Http\Resources\InformationResource;
use App\Models\Information;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class InformationsService
{
    public function __construct(private ImageService $imageService, private GlobalService $globalService) {}


    public function getInformations()
    {
        try {
            $informations = Information::select(['uuid', 'title', 'icon_name', 'content'])->orderBy('created_at', 'desc')->get();
            return InformationResource::collection($informations);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return HttpStatusEnum::ERROR;
        }
    }
    public function createInformation(string $title, string $content, UploadedFile $image, string $icon_name)
    {
        try {
            DB::beginTransaction();
            $image = $this->imageService->storeImage($image);
            $model = Information::create([
                'title' => $title,
                'content' => "",
                'icon_name' => $icon_name,
            ]);
            $model->previewImage()->create([
                'image_name' => $image['randomFileName'],
                'image_path' =>  $image['imagePath'],
                'image_type' => $image['extension'],
                'image_file_name' => $image['originalName']
            ]);
            $content = $this->globalService->updateContent($content, $model);
            $model->content = $content;
            $model->save();
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
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
                $newIMage = $this->imageService->updateImage($information->previewImage->id, $updateArray['image']);
                unset($updateArray['image']);
                $information->refresh();
            }
            if (array_key_exists('content', $updateArray)) {
                $content = $updateArray['content'];
                foreach ($information->images as $image) {
                    if (!str_contains($content, $image->image_name)) {
                        $image->delete($image->id);
                    }
                }
                $updateArray['content'] = $this->globalService->updateContent($content, $information);
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
