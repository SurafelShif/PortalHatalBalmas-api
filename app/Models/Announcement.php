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
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }
        });
    }
    protected $fillable = [
        'title',
        'description',
        'content',
        'image_id',
        'position'
    ];
    protected $hidden = ['updated_at', 'id'];

    protected $casts = [
        'content' => 'array',
    ];
}
