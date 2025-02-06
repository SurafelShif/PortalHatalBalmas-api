<?php

namespace App\Models;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    public function image()
    {
        return $this->hasOne(Image::class, 'id', 'image_id');
    }
    public function category()
    {
        return $this->hasOne(Category::class, 'id', 'category_id');
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
            $post->image()->delete();
        });
    }
    protected $fillable = [
        'title',
        'description',
        'content',
        'image_id',
        'category_id',
    ];
    protected $hidden = ['updated_at', 'created_at', 'id'];

    protected $casts = [
        'content' => 'array',
    ];
}
