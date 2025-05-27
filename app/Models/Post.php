<?php

namespace App\Models;

use App\Enums\ImageTypeEnum;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    public function previewImage()
    {
        return $this->morphOne(Image::class, 'imageable')->where('type', ImageTypeEnum::PREVIEW_IMAGE->value);
    }
    public function images()
    {
        return $this->morphMany(Image::class, 'imageable')->where('type', ImageTypeEnum::CONTENT_IMAGE->value);
    }
    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id', 'id');
    }
    //
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }
        });
        static::deleting(function ($post) {

            if ($post->previewImage) {
                $post->previewImage->delete();
            }
            $post->images->each(function ($image) {
                $image->delete();
            });
        });
    }
    protected $fillable = [
        'title',
        'description',
        'content',
        'preview_image_id',
        'category_id',
    ];
    protected $hidden = ['updated_at', 'id'];
}
