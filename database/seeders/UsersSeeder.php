<?php

namespace Database\Seeders;

use App\Enums\Permission;
use App\Enums\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class UsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admins = [
            ['personal_id' => '122345671', 'full_name' => 'מנהל מערכת 1'],
            ['personal_id' => '112345678', 'full_name' => 'מנהל מערכת 2'],
            ['personal_id' => '123456789', 'full_name' => 'מנהל מערכת 3'],
        ];

        foreach ($admins as $adminData) {
            $admin = User::create($adminData);
            $admin->assignRole(Role::ADMIN);
            $admin->givePermissionTo(Permission::MANAGE_USERS);
        }

        $users = [
            ['personal_id' => '987654321', 'full_name' => 'משתמש רגיל 1'],
            ['personal_id' => '876543210', 'full_name' => 'משתמש רגיל 2'],
            ['personal_id' => '765432109', 'full_name' => 'משתמש רגיל 3'],
        ];

        foreach ($users as $userData) {
            User::create($userData);
        }
    }
}
