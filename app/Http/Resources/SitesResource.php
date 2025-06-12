<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SitesResource extends JsonResource
{
    public function toArray(Request $request)
    {
        return [
            'uuid' => $this->uuid,
            'name' => $this->name,
            'description' => $this->description,
            'link' => $this->link,
            'image' => $this->previewImage?->image_path ? config('filesystems.storage_path') . $this->previewImage->image_path : null,
            'image_name' => $this->previewImage?->image_path ? $this->previewImage->image_file_name : null,
        ];
    }
}
