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
    public function createAnnouncement(string $title, string $description, string $content, UploadedFile $image)
    {
        $createdImages = [];

        try {
            DB::beginTransaction();
            preg_match_all('/data:image\/(.*?);base64,(.*?)"/', $content, $matches);
            if (!empty($matches[2])) {
                $createdImages = $this->createImages($matches[2], $matches[1]);

                foreach ($matches[2] as $index => $base64Data) {
                    $oldString = "data:image/{$matches[1][$index]};base64,{$base64Data}\"";
                    $newString = config('filesystems.storage_path') . 'images/' . $createdImages[$index] . '"';

                    $content = str_replace($oldString, $newString, $content);
                }
            }

            $createdImage = $this->imageService->uploadImage($image);
            $createdImages[] = $createdImage->image_name;
            Announcement::create([
                'title' => $title,
                'description' => $description,
                'content' => $content,
                'image_id' => $createdImage->id
            ]);
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
            $annoucements = Announcement::orderBy('position', 'asc')->where('isVisible', true)->select(['uuid', 'title', 'description', 'image_id'])->get();
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
                ->select(['uuid', 'title', 'description', 'position', 'image_id', 'created_at', 'isVisible', 'content'])
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
                $this->imageService->updateImage($announcement->image->id, $updateArray['image']);
                unset($updateArray['image']);
                $announcement->refresh();
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
            $announcement = Announcement::select(['uuid', 'title', 'image_id', 'created_at', 'content'])->where('uuid', $uuid)->first();
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
            return response()->json(['error' => 'Something went wrong'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    private function createImages(array $images, array $types)
    {
        try {
            foreach ($images as $index => $image) {
                $created_image = $this->imageService->uploadStringImage(base64_decode($image), $types[$index]);
                $created_images[] = $created_image->image_name;
            }
            return $created_images;
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return response()->json(['error' => 'Something went wrong'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
