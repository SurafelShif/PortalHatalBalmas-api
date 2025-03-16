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
            'image' => $this->image->image_path ? config('filesystems.storage_path') . $this->image->image_path : null,
            'image_name' => $this->image->image_path ? $this->image->image_file_name : null,

        ], fn($value) => !is_null($value));
    }
}
