<?php

namespace Database\Seeders;

use App\Models\Image;
use App\Models\Information;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;

class InformationsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create();
        for ($i = 0; $i < 5; $i++) {

            Information::create([
                'icon_name' => 'Apple',
                'title' => $faker->sentence(6),
                'content' => '<p><span style="color: #D64C4C"><em>רפאל האלוף</em></span></p>',
            ]);
        }
    }
}
