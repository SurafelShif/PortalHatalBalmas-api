<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PostResource extends JsonResource
{
    public function toArray(Request $request)
    {

        return array_filter([
            'uuid' => $this->uuid,
            'title' => $this->title,
            'description' => $this->description,
            'category' => $this->category ? ["name" => $this->category->name, "id" => $this->category->id] : null,
            'content' => $this->content ? $this->content : null,
            'image' => optional($this->image)->image_path
                ?  config('filesystems.storage_path') . $this->image->image_path
                : null,
            "created_at" => $this->created_at ? $this->created_at->format('H:i d/m/Y') : null,

        ], fn($value) => !is_null($value));
    }
}
