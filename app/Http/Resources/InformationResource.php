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
        if (is_null($this->content)) {
            $content = null;
        } else {
            $content = !count($this->content) ? (object) [] : $this->content;
        }
        return array_filter([
            'uuid' => $this->uuid,
            'title' => $this->title,
            'content' => $content,
            'image' => $this->image->image_path ? config('filesystems.storage_path') . $this->image->image_path : null,

        ], fn($value) => !is_null($value));
    }
}
