<?php

namespace App\Services;

use App\Enums\HttpStatusEnum;
use App\Http\Resources\AnnoucementsResource;
use App\Models\Announcement;
use Symfony\Component\HttpFoundation\Response;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
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
            $annoucements = Announcement::orderBy('position', 'asc')->get();
            return AnnoucementsResource::collection($annoucements);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return HttpStatusEnum::ERROR;
        }
    }
    public function updateAnnouncement(array $updateArray)
    {
        try {
            DB::beginTransaction();
            foreach ($updateArray as $updateInfo) {
                $announcement = Announcement::where('uuid', $updateInfo['uuid'])->first();
                if (is_null($announcement)) {
                    DB::rollBack();
                    return HttpStatusEnum::NOT_FOUND;
                }
                if (array_key_exists('image', $updateInfo)) {
                    $this->imageService->updateImage($announcement->image->id, $updateInfo['image']);
                    unset($updateInfo['image']);
                }
                if (array_key_exists('content', $updateInfo)) {
                    $updateInfo['content'] = json_decode($updateInfo['content'], 1);
                }
                $announcement->update($updateInfo);
            }
            DB::commit();
            return Response::HTTP_OK;
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return HttpStatusEnum::ERROR;
        }
    }
    public function deleteAnnouncement(string $uuid)
    {
        try {
            $announcement = Announcement::where('uuid', $uuid)->first();
            if (is_null($announcement)) {
                return HttpStatusEnum::NOT_FOUND;
            }
            Announcement::destroy($announcement->id);
            return Response::HTTP_OK;
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return HttpStatusEnum::ERROR;
        }
    }
    public function updateAnnouncementVisibility($uuid, bool $isVisible)
    {
        try {
            $announcement = Announcement::where('uuid', $uuid)->first();

            if (is_null($announcement)) {
                return HttpStatusEnum::NOT_FOUND;
            }
            $announcement->isVisible = $isVisible;
            $announcement->save();
            return Response::HTTP_OK;
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return HttpStatusEnum::ERROR;
        }
    }
}
