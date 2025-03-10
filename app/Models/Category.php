<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $fillable = ['name', 'filter_by'];
    protected $hidden = ['updated_at', 'created_at'];

    public function posts()
    {
        return $this->hasMany(Post::class);
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $maxFilterBy = Category::max('filter_by') ?? -1; // Start from 0 if null
            $model->filter_by = $maxFilterBy + 1;
        });

        static::deleting(function ($model) {
            Category::where('filter_by', '>', $model->filter_by)
                ->decrement('filter_by');
        });
    }
}
