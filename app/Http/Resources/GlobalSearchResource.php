<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GlobalSearchResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return array_filter([
            'uuid' => $this->uuid,
            'title' => $this->title,
            'content' => $this->content ? $this->content : null,
            'type' => $this->type,
            'name' => $this->name,
            'link' => $this->link ? $this->link : null,
            'category' => $this->category ? ["name" => $this->category->name, "id" => $this->category->id] : null,
            'image' => optional($this->image)->image_path
                ?  config('filesystems.storage_path') . $this->image->image_path
                : null,
        ], fn($value) => !is_null($value));
    }
}
