<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InformationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return array_filter([
            'uuid' => $this->uuid,
            'title' => $this->title,
            'content' => $this->content,
            'icon_name' => $this->icon_name,
            'image' => $this->previewImage ? config('filesystems.storage_path') . $this->previewImage->image_path : null,


        ], fn($value) => !is_null($value));
    }
}
