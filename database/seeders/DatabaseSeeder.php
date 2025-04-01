<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(RoleAndPermissionSeeder::class);
        $this->call(UsersSeeder::class);
        // $this->call(PostsSeeder::class);
        // $this->call(AnnouncementsSeeder::class);
        // $this->call(SitesSeeder::class);
        // $this->call(InformationsSeeder::class);
    }
}
