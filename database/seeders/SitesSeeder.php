<?php

namespace Database\Seeders;

use App\Models\Image;
use App\Models\Site;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;

class SitesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create();
        for ($i = 0; $i < 5; $i++) {

            Site::create([
                'icon_name' => 'Apple',
                'name' => $faker->sentence(6),
                'description' => $faker->sentence(10),
                'link' => $faker->url()
            ]);
        }
    }
}
