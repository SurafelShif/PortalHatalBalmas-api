<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Image;
use App\Models\Posts;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;

use Faker\Factory as Faker;

class PostsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create();
        $categories = ["מפקדת מחנה", 'משא"ן', "מחשב"];
        for ($i = 0; $i < 5; $i++) {
            $image = Image::create([
                "image_name" => "moon.jpg",
                "image_type" => "jpg",
                "image_path" => "images/moon.jpg"
            ]);
            $category = Category::create([
                'name' => Arr::random($categories)
            ]);
            Posts::create([
                'image_id' => $image->id,
                'title' => $faker->sentence(6),
                'description' => $faker->sentence(10),
                'content' => $faker->paragraph(5),
                'category_id' => $category->id
            ]);
        }
    }
}
