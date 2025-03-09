<?php

namespace Database\Seeders;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        $admin1 = User::create([
            'personal_id' => '12234567',
            'full_name' => 'מנהל מערכת 1',

        ]);
        $admin1->assignRole(Role::ADMIN);

        $admin2 = User::create([
            'personal_id' => '112345678',
            'full_name' => 'מנהל מערכת 2',
        ]);
        $admin2->assignRole(Role::ADMIN);

        // admin 3
        $admin3 = User::create([
            'personal_id' => '123456789',
            'full_name' => 'מנהל מערכת 3',
        ]);
        $admin3->assignRole(Role::ADMIN);
    }
}
