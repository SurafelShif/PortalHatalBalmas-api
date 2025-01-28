<?php

namespace Database\Seeders;

use App\Models\Image;
use App\Models\News;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class NewsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $image = Image::create([
                'image_name' => null,
                'image_type' => null,
                'image_path' => null
            ]);
            News::create([
                'image_id' => $image->id,
                'title' => Str::random(20),
                'description' => Str::random(20),
                'content' => Str::random(50),

            ]);
        }
    }
}
