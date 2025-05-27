<?php

namespace App\Services;

use App\Enums\HttpStatusEnum;
use App\Enums\ImageTypeEnum;
use App\Http\Resources\AnnoucementsResource;
use App\Models\Announcement;
use Illuminate\Database\Eloquent\Model;
use Symfony\Component\HttpFoundation\Response;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AnnouncementsService
{
    public function __construct(private ImageService $imageService, private GlobalService $globalService) {}
    public function createAnnouncement(string $title, string $description, string $content, UploadedFile $image)
    {
        $createdImages = [];
        try {
            DB::beginTransaction();
            $createdImage = $this->imageService->storeImage($image);
            $createdImages[] = $createdImage['randomFileName'];
            $model = Announcement::create([
                'title' => $title,
                'description' => $description,
                'content' => $content,
            ]);
            $this->imageService->saveImage($model, $createdImage, ImageTypeEnum::PREVIEW_IMAGE->value);
            $this->globalService->commitContentImages($model, $content);
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            foreach ($createdImages as $image) {
                $this->imageService->deleteImage($image);
            }
            Log::error($e->getMessage());
            return HttpStatusEnum::ERROR;
        }
    }
    public function getAnnouncements()
    {
        try {
            $annoucements = Announcement::select(['id', 'uuid', 'title', 'description'])->with('previewImage')->orderBy('position', 'asc')->where('isVisible', true)->get();
            return AnnoucementsResource::collection($annoucements);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return HttpStatusEnum::ERROR;
        }
    }
    public function getAdminAnnouncements(?string $search)
    {
        try {
            $announcements = Announcement::orderBy('position', 'asc')
                ->select(['id', 'uuid', 'title', 'description', 'position', 'created_at', 'isVisible', 'content'])
                ->when(!empty($search), function ($query) use ($search) {
                    $query->where('title', 'LIKE', "%{$search}%")
                        ->orWhere('description', 'LIKE', "%{$search}%");
                })

                ->get();

            return AnnoucementsResource::collection($announcements);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return HttpStatusEnum::ERROR;
        }
    }
    public function updateAnnouncement(string $uuid, array $updateArray)
    {

        try {

            DB::beginTransaction();
            $announcement = Announcement::where('uuid', $uuid)->first();
            if (is_null($announcement)) {
                return HttpStatusEnum::NOT_FOUND;
            }
            if (array_key_exists('image', $updateArray)) {
                $this->imageService->updateImage($announcement->previewImage->id, $updateArray['image']);
                unset($updateArray['image']);
                $announcement->refresh();
            }
            if (array_key_exists('content', $updateArray)) {
                $this->globalService->commitContentImages($announcement, $updateArray['content']);
                $this->globalService->removeCommitedContentImages($announcement, $updateArray['content']);
            }
            $announcement->update($updateArray);
            DB::commit();
            return new AnnoucementsResource($announcement);
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
            $announcement = Announcement::select(['id', 'uuid', 'title', 'created_at', 'content'])->where('uuid', $uuid)->first();
            if (is_null($announcement)) {
                return HttpStatusEnum::NOT_FOUND;
            }
            return new AnnoucementsResource($announcement);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return HttpStatusEnum::ERROR;
        }
    }
    public function updateAnnouncementPosition(array $positions)
    {
        try {
            foreach ($positions as $item) {
                $announcement = Announcement::where('uuid', $item['uuid'])->first();

                $announcement->position = $item['position'];
                $announcement->save();
            }

            return Response::HTTP_OK;
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return HttpStatusEnum::ERROR;
        }
    }
}
