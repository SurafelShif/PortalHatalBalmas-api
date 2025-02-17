<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AnnoucementsResource extends JsonResource
{
    public function toArray(Request $request)
    {
        return array_filter([
            'uuid' => $this->uuid,
            'title' => $this->title,
            'description' => $this->description,
            'content' => $this->content,
            'position' => $this->position,
            'isVisible' => $this->isVisible,
            'image' => $this->image->image_path ? config('filesystems.storage_path') . $this->image->image_path : null,
            'created_at' => $this->created_at ? $this->created_at->format('d/m/Y H:i') : null,
        ], fn($value) => !is_null($value));
    }
}
