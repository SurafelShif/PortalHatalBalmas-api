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
    public function storeImage(UploadedFile $image)
    {
        $extension = $image->getClientOriginalExtension();
        $originalName = $image->getClientOriginalName();
        $randomFileName = uniqid() . '_' . Str::random(10) . '.' . $extension;
        $imagePath = $image->storeAs('images', $randomFileName, config('filesystems.storage_service'));
        return [
            "extension" => $extension,
            "originalName" => $originalName,
            "randomFileName" => $randomFileName,
            "imagePath" => $imagePath
        ];
    }
    public function saveImage(Model $model, array $createdImage)
    {
        $model->previewImage()->create([
            'image_name' => $createdImage['randomFileName'],
            'image_path' =>  $createdImage['imagePath'],
            'image_type' => $createdImage['extension'],
            'image_file_name' => $createdImage['originalName']
        ]);
    }
    public function uploadImage(UploadedFile $image)
    {
        try {

            $extension = $image->getClientOriginalExtension();
            $originalName = $image->getClientOriginalName();
            $randomFileName = uniqid() . '_' . Str::random(10) . '.' . $extension;
            $imagePath = $image->storeAs('images', $randomFileName, config('filesystems.storage_service'));
            return Image::create([
                'image_name' => $randomFileName,
                'image_path' => $imagePath,
                'image_type' => $image->getMimeType(),
                'image_file_name' => $originalName
            ]);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return HttpStatusEnum::ERROR;
        }
    }

    public function updateImage($associatedimageId, UploadedFile | null $newImage)
    {
        try {

            $oldImage = Image::find($associatedimageId);
            if (is_null($oldImage) && is_null($newImage)) {
                return;
            }
            if (is_null($oldImage)) {
                return $this->uploadImage($newImage);
            }
            if ($newImage !== null) {
                $extension = $newImage->getClientOriginalExtension();
                $randomFileName = uniqid() . '_' . Str::random(10) . '.' . $extension;
                $imagePath = $newImage->storeAs('images', $randomFileName, config('filesystems.storage_service'));
                $originalName = $newImage->getClientOriginalName();
            } else {
                $imagePath = null;
                $randomFileName = null;
                $extension = null;
                $originalName = null;
            }
            $this->deleteImage($oldImage->image_name);
            $oldImage->image_path = $imagePath;
            $oldImage->image_name = $randomFileName;
            $oldImage->image_type = $extension;
            $oldImage->image_file_name = $originalName;
            $oldImage->save();
            return $oldImage;
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return HttpStatusEnum::ERROR;
        }
    }
    public function deleteImage($image_name)
    {
        try {
            if (Storage::disk(config('filesystems.storage_service'))->exists('images/' . $image_name)) {
                Storage::disk(config('filesystems.storage_service'))->delete('images/' . $image_name);
            } else {
                Log::info("image was not found");
            }
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return HttpStatusEnum::ERROR;
        }
    }
    public function uploadStringImage(string $image, $extension, Model $model)
    {
        try {
            $randomFileName = uniqid() . '_' . Str::random(10) . '.' . $extension;
            Storage::disk(config('filesystems.storage_service'))->put('images/' . $randomFileName, $image);
            return $model->images()->create([
                'image_name' => $randomFileName,
                'image_path' => 'images/' . $randomFileName,
                'image_type' => $extension,
                'image_file_name' => null
            ]);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return HttpStatusEnum::ERROR;
        }
    }
}
