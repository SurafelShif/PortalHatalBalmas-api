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
            'category' => $this->category ? ["name" => $this->category->name, "uuid" => $this->category->uuid] : null,
            'content' =>  $this->content,
            'image' => $this->previewImage->image_path ? config('filesystems.storage_path') . $this->previewImage->image_path : null,
            'image_name' => $this->previewImage->image_path ? $this->previewImage->image_file_name : null,
            "created_at" => $this->created_at ? $this->created_at->format('H:i d/m/Y') : null,

        ], fn($value) => !is_null($value));
    }
}
