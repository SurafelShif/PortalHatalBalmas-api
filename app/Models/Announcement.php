<?php

namespace App\Models;

use App\Enums\ImageTypeEnum;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;

class Announcement extends Model
{
    public function previewImage()
    {
        return $this->morphOne(Image::class, 'imageable')->where('type', ImageTypeEnum::PREVIEW_IMAGE->value);
    }
    public function images()
    {
        return $this->morphMany(Image::class, 'imageable')->where('type', ImageTypeEnum::CONTENT_IMAGE->value);
    }
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }
            Announcement::query()->increment('position');
            $model->position = 1;
        });
        static::deleting(function ($announcement) {
            if ($announcement->previewImage) {
                $announcement->previewImage->delete();
            }
            $announcement->images->each(function ($image) {
                $image->delete();
            });
        });
    }
    protected $fillable = [
        'title',
        'description',
        'content',
        'isVisible',
        'position',
    ];
    protected $hidden = ['updated_at', 'id'];

    protected $casts = [
        'isVisible' => 'boolean'
    ];
}
