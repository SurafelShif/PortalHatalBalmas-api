<?php

namespace Database\Seeders;

use App\Models\Announcement;
use App\Models\Image;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;

class AnnouncementsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create();
        for ($i = 0; $i < 5; $i++) {
            $image = Image::create([
                "image_name" => "moon.jpg",
                "image_type" => "jpg",
                "image_path" => "images/moon.jpg",
                "image_file_name" => "moon.jpg"
            ]);
            Announcement::create([
                'image_id' => $image->id,
                'title' => $faker->sentence(6),
                'description' => $faker->sentence(10),
                'content' => '<p><span style="color: #D64C4C"><em>רפאל האלוף</em></span></p>',
                'position' => $i
            ]);
        }
    }
}
