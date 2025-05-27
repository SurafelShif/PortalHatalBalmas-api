<?php

namespace App\Models;

use App\Enums\ImageTypeEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Information extends Model
{
    protected $table = 'informations';
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
        });
        static::deleting(function ($information) {

            if ($information->previewImage) {
                $information->previewImage->delete();
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
    ];
    protected $hidden = ['updated_at', 'created_at', 'id'];
}
