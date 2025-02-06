<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Site extends Model
{
    //
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }
        });
        static::deleting(function ($site) {
            $site->image()->delete();
        });
    }
    public function image()
    {
        return $this->hasOne(Image::class, 'id', 'image_id');
    }
    protected $fillable = [
        "name",
        "description",
        "link",
        "image_id"
    ];
    protected $hidden = [
        "created_at",
        "updated_at",
        "id"
    ];
}
