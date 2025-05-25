<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Information extends Model
{
    protected $table = 'informations';
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
        });
        static::deleting(function ($information) {

            if ($information->image) {
                $information->image->delete();
            }
            $information->images->each(function ($image) {
                $image->delete();
            });
        });
    }
    protected $fillable = [
        'title',
        'content',
        'icon_name',
        'preview_image_id',
    ];
    protected $hidden = ['updated_at', 'created_at', 'id'];
}
