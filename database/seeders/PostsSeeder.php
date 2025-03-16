<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Image;
use App\Models\Post;
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
        foreach ($categories as $index => $category) {
            $category = Category::create([
                'name' => $category,
                'filter_by' => $index
            ]);
        }
        for ($i = 0; $i < 5; $i++) {
            $image = Image::create([
                "image_name" => "moon.jpg",
                "image_type" => "jpg",
                "image_path" => "images/moon.jpg",
                "image_file_name" => "moon.jpg"
            ]);

            Post::create([
                'image_id' => $image->id,
                'title' => $faker->sentence(6),
                'description' => $faker->sentence(10),
                'content' => json_encode(["some" => $faker->paragraph(5)]),
                'category_id' => Arr::random([1, 2, 3])
            ]);
        }
    }
}
