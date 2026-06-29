<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ensure Spatie Roles exist
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $studentRole = Role::firstOrCreate(['name' => 'student']);

        // Create Admin User
        $admin = User::updateOrCreate(
            ['email' => 'admin@examsninja.com'],
            [
                'name' => 'ExamsNinja Admin',
                'password' => Hash::make('Admin@1234'),
                'role' => 'admin',
                'email_verified_at' => now(),
            ]
        );
        $admin->assignRole($adminRole);

        // Create Student User
        $student = User::updateOrCreate(
            ['email' => 'student@examsninja.com'],
            [
                'name' => 'John Student',
                'password' => Hash::make('Student@1234'),
                'role' => 'student',
                'email_verified_at' => now(),
            ]
        );
        $student->assignRole($studentRole);
    }
}
