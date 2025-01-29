<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Image;
use App\Models\Posts;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PostsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $image = Image::create([
                "image_name" => "moon.jpg",
                "image_type" => "jpg",
                "image_path" => "images/moon.jpg"
            ]);
            $category = Category::create([
                'name' => 'some'
            ]);
            Posts::create([
                'image_id' => $image->id,
                'title' => Str::random(20),
                'description' => Str::random(20),
                'content' => Str::random(50),
                'category_id' => $category->id
            ]);
        }
    }
}
