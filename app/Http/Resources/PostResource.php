<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PostResource extends JsonResource
{
    public function toArray(Request $request)
    {
        return [
            'uuid' => $this->uuid,
            'title' => $this->title,
            'description' => $this->description,
            'content' => $this->content,
            'image' => $this->image->image_path ? config('filesystems.storage_path') . $this->image->image_path : null,
            'category' => [
                'name' => $this->category?->name,
                'id' => $this->category?->id
            ]

        ];
    }
}
