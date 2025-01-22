<?php

namespace Database\Seeders;

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
        $user = User::create([
            'personal_id' => '000000000',
            'full_name' => 'משתמש רגיל חט"ל',

        ]);
        // $user->assignRole(Role::USER);

        $user2 = User::create([
            'personal_id' => '111111111',
            'full_name' => 'משתמש רגיל 1 חט"ל',

        ]);
        // $user2->assignRole(Role::USER);

        $user3 = User::create([
            'personal_id' => '222222222',
            'full_name' => 'משתמש רגיל 2 חט"ל',
        ]);
        // $user3->assignRole(Role::USER);

        $admin1 = User::create([
            'personal_id' => '12234567',
            'full_name' => 'מנהל מערכת 1',

        ]);
        // $admin1->assignRole(Role::ADMIN);

        $admin2 = User::create([
            'personal_id' => '112345678',
            'full_name' => 'מנהל מערכת 2',
        ]);
        // $admin2->assignRole(Role::ADMIN);

        // admin 3
        $admin3 = User::create([
            'personal_id' => '123456789',
            'full_name' => 'מנהל מערכת 3',
        ]);
        // $admin3->assignRole(Role::ADMIN);
    }
}
