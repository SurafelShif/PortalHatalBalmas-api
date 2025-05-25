<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Image extends Model
{
    use HasFactory;
    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($image) {
            if (Storage::disk(config('filesystems.storage_service'))->exists('images/' . $image->image_name)) {
                Storage::disk(config('filesystems.storage_service'))->delete('images/' . $image->image_name);
            }
        });
    }
    public function image()
    {
        return $this->morphTo();
    }
    public function imageable()
    {
        return $this->morphTo();
    }
    protected $fillable = [
        'image_name',
        'image_type',
        'image_path',
        'image_file_name',
    ];
    protected $hidden = [
        'created_at',
        'updated_at',
        'is_deleted',
        'id'
    ];
}
