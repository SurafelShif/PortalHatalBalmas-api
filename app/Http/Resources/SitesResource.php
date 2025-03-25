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
            'image' => $this->image->image_path ? config('filesystems.storage_path') . $this->image->image_path : null,
        ];
    }
}
