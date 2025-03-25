<?php

namespace App\Models;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;

class Announcement extends Model
{
    public function image()
    {
        return $this->hasOne(Image::class, 'id', 'image_id');
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
            $announcement->image()->delete();
        });
    }
    protected $fillable = [
        'title',
        'description',
        'content',
        'image_id',
        'isVisible',
        'position'
    ];
    protected $hidden = ['updated_at', 'id'];

    protected $casts = [
        'isVisible' => 'boolean'
    ];
}
