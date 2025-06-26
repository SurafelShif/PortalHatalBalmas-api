<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class Image extends Model
{
    use HasFactory;
    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($image) {

            if (Storage::disk(config('filesystems.storage_service'))->exists('public/' . $image->image_name)) {
                Storage::disk(config('filesystems.storage_service'))->delete('public/' . $image->image_name);
            } else {
                Log::info("image was not found");
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
        'is_commited',
        'imageable_id',
        'imageable_type',
        'type'
    ];

    protected $casts = [
        'is_commited' => 'boolean',
    ];
    protected $hidden = [
        // 'created_at',
        // 'updated_at',
        'id'
    ];
}
