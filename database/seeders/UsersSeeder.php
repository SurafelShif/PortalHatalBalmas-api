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
            ['personal_id' => '328701503', 'full_name' => 'סוראפל שיפראוו'],
            ['personal_id' => '204030373', 'full_name' => 'גל לחגי'],
            ['personal_id' => '206094468', 'full_name' => 'רועי סחייק'],
            ['personal_id' => '315552810', 'full_name' => 'נתי אפרים'],
        ];

        foreach ($admins as $adminData) {
            $admin = User::create($adminData);
            $admin->assignRole(Role::ADMIN);
            $admin->givePermissionTo(Permission::MANAGE_USERS);
        }

        $users = [
            ['personal_id' => '315141325', 'full_name' => 'אופיר גולדברג'],
            ['personal_id' => '325639797', 'full_name' => 'ליאון לב'],
        ];

        foreach ($users as $userData) {
            User::create($userData);
        }
    }
}
