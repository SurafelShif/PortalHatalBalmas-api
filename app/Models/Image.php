<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Image extends Model
{
    use HasFactory;
    public function imageable()
    {
        return $this->morphTo();
    }
    protected $fillable = [
        'image_name',
        'image_type',
        'image_path',
        'image_file_name',
        'imageable_id',
        'imageable_type'
    ];
    protected $hidden = [
        'created_at',
        'updated_at',
        'is_deleted',
        'id'
    ];
}
