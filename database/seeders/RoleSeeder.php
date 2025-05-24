<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

use App\Models\User;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create roles
        $superAdmin = Role::firstOrCreate(['name' => 'Super Admin']);
        $admin = Role::firstOrCreate(['name' => 'Admin']);
        $staff = Role::firstOrCreate(['name' => 'Staff']);

        // Create a Super Admin user (if not already exists)
        $user = User::firstOrCreate(
            ['email' => 'dev@lapasys.test'],
            [
                'name' => 'Developer',
                'username' => 'devadmin',
                'password' => bcrypt('password'),
            ]
        );

        $user->assignRole('Super Admin');
    }
}
