<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Information extends Model
{
    protected $table = 'informations';
    public function image()
    {
        return $this->hasOne(Image::class, 'id', 'image_id');
    }
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
        'content',
        'image_id',
    ];
    protected $hidden = ['updated_at', 'created_at', 'id'];

    protected $casts = [
        'content' => 'array',
    ];
}
