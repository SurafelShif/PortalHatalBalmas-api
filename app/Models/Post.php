<?php

namespace App\Models;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    public function previewImage()
    {
        return $this->morphOne(Image::class, 'imageable')->where('type', 'preview');
    }
    public function images()
    {
        return $this->morphMany(Image::class, 'imageable')->where('type', 'content');
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
