<?php

namespace App\Models;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;

class Announcement extends Model
{
    public function previewImage()
    {
        return $this->morphOne(Image::class, 'imageable');
    }
    public function images()
    {
        return $this->morphMany(Image::class, 'imageable');
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
