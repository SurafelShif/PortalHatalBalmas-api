<?php

namespace App\Services;

use App\Enums\HttpStatusEnum;
use App\Models\Image;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;


class ImageService
{
    private const STORAGE_DIR = 'public';
    private const DEFAULT_TYPE = 'content';
    public function storeImage(UploadedFile $image)
    {
        try {
            return $this->processImageUpload($image);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return HttpStatusEnum::ERROR;
        }
    }
    public function saveImage(Model $model, array $createdImage, string $type)
    {
        try {
            $model->previewImage()->create([
                'image_name' => $createdImage['randomFileName'],
                'image_path' =>  $createdImage['imagePath'],
                'image_type' => $createdImage['extension'],
                'image_file_name' => $createdImage['originalName'],
                'is_commited' => true,
                'type' => $type
            ]);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return HttpStatusEnum::ERROR;
        }
    }
    public function uploadImage(UploadedFile $image): Image|HttpStatusEnum
    {
        try {
            $data = $this->processImageUpload($image);

            return Image::create([
                'image_name'      => $data['randomFileName'],
                'image_path'      => $data['imagePath'],
                'image_type'      => $image->getMimeType(),
                'image_file_name' => $data['originalName'],
                'type'            => self::DEFAULT_TYPE,
            ]);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return HttpStatusEnum::ERROR;
        }
    }

    public function updateImage(int $imageId, ?UploadedFile $newImage): Image|HttpStatusEnum|null
    {
        try {

            $existingImage = Image::find($imageId);

            if (is_null($existingImage) && is_null($newImage)) {
                return null;
            }
            if (is_null($existingImage)) {
                return $this->uploadImage($newImage);
            }
            if ($newImage !== null) {
                $data = $this->processImageUpload($newImage);

                $this->deleteImage($existingImage->image_name);

                $existingImage->update([
                    'image_path'      => $data['imagePath'],
                    'image_name'      => $data['randomFileName'],
                    'image_type'      => $data['extension'],
                    'image_file_name' => $data['originalName'],
                ]);
            }
            return $existingImage;
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return HttpStatusEnum::ERROR;
        }
    }
    public function deleteImage(string $imageName): bool|HttpStatusEnum
    {
        try {
            $fullPath = self::STORAGE_DIR . '/' . $imageName;
            $disk = Storage::disk(config('filesystems.storage_service'));

            if ($disk->exists($fullPath)) {
                return $disk->delete($fullPath);
            }

            Log::info("Image not found: {$fullPath}");
            return false;
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return HttpStatusEnum::ERROR;
        }
    }
    private function processImageUpload(UploadedFile $image): array
    {
        $extension = $image->getClientOriginalExtension();
        $originalName = $image->getClientOriginalName();
        $randomFileName = uniqid() . '_' . Str::random(10) . '.' . $extension;
        $imagePath = $image->storeAs(self::STORAGE_DIR, $randomFileName, config('filesystems.storage_service'));

        return compact('extension', 'originalName', 'randomFileName', 'imagePath');
    }
}
