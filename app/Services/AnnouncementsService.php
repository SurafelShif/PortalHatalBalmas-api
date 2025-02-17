<?php

namespace App\Services;

use App\Enums\HttpStatusEnum;
use App\Http\Resources\AnnoucementsResource;
use App\Models\Announcement;
use Symfony\Component\HttpFoundation\Response;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AnnouncementsService
{
    public function __construct(private ImageService $imageService) {}
    public function createAnnouncement(string $title, string $description, string $content, UploadedFile $image)
    {
        $createdImage = null;
        try {
            $createdImage = $this->imageService->uploadImage($image);
            Announcement::create([
                'title' => $title,
                'description' => $description,
                'content' => json_decode($content, 1),
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
            $annoucements = Announcement::orderBy('position', 'asc')->where('isVisible', true)->select(['uuid', 'title', 'description', 'image_id'])->get();
            return AnnoucementsResource::collection($annoucements);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return HttpStatusEnum::ERROR;
        }
    }
    public function getAdminAnnouncements()
    {
        try {
            $annoucements = Announcement::orderBy('position', 'asc')->select(['uuid', 'title', 'description', 'position', 'image_id', 'created_at', 'isVisible'])->get();
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
            if ($isVisible === $announcement->isVisible) {
                return HttpStatusEnum::NO_CONTENT;
            }
            if ($isVisible) {
                $maxPosition = Announcement::max('position');
                $announcement->position = $maxPosition ? $maxPosition + 1 : 1;
            } else {
                Announcement::where('position', '>', $announcement->position)
                    ->decrement('position');
                $announcement->position = null;
            }
            $announcement->isVisible = $isVisible;
            $announcement->save();
            return Response::HTTP_OK;
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return HttpStatusEnum::ERROR;
        }
    }
    public function getAnnouncementByUUid($uuid)
    {
        try {
            // if (!Str::isUuid($uuid)) {
            //     return HttpStatusEnum::BAD_REQUEST;
            // }
            $announcement = Announcement::select(['uuid', 'title', 'image_id', 'created_at', 'content'])->where('uuid', $uuid)->first();
            if (is_null($announcement)) {
                return HttpStatusEnum::NOT_FOUND;
            }
            $user = Auth::user();
            if (is_null($user) && !$announcement->isVisible) {
                return HttpStatusEnum::FORBIDDEN;
            }
            return new AnnoucementsResource($announcement);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return HttpStatusEnum::ERROR;
        }
    }
}
