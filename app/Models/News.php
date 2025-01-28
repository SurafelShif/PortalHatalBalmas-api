<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class News extends Model
{
    //
    protected $fillable = [
        'title',
        'description',
        'content',
        'image_id',
    ];
    protected $hidden = ['updated_at', 'created_at', 'id'];
}
