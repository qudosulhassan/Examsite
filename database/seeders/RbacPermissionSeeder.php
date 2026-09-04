<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;

class RbacPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // 1. Define Granular Permissions by Group
        $permissionGroups = [
            'Dashboard' => [
                'view-dashboard',
            ],
            'Users' => [
                'view-users',
                'create-users',
                'edit-users',
                'delete-users',
                'suspend-users',
                'manage-roles',
                'manage-permissions',
                'export-users',
                'view-audit-logs',
            ],
            'Vendors' => [
                'view-vendors',
                'create-vendors',
                'edit-vendors',
                'delete-vendors',
            ],
            'Exams' => [
                'view-exams',
                'create-exams',
                'edit-exams',
                'delete-exams',
            ],
            'Questions' => [
                'view-questions',
                'create-questions',
                'edit-questions',
                'delete-questions',
                'import-questions',
                'export-questions',
            ],
            'Orders' => [
                'view-orders',
                'manage-orders',
                'refund-orders',
            ],
            'Subscriptions' => [
                'view-subscriptions',
                'manage-subscriptions',
            ],
            'Coupons' => [
                'view-coupons',
                'manage-coupons',
            ],
            'Blog' => [
                'view-posts',
                'create-posts',
                'edit-posts',
                'delete-posts',
                'manage-comments',
                'manage-subscribers',
            ],
            'Media' => [
                'manage-media',
            ],
            'Settings' => [
                'view-settings',
                'manage-settings',
            ],
            'Reports' => [
                'view-reports',
            ],
        ];

        $allPermissions = [];
        foreach ($permissionGroups as $group => $permissions) {
            foreach ($permissions as $permissionName) {
                $p = Permission::firstOrCreate(['name' => $permissionName, 'guard_name' => 'web']);
                $allPermissions[] = $p;
            }
        }

        // 2. Define Core Roles
        $superAdminRole = Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'web']);
        $adminRole      = Role::firstOrCreate(['name' => 'Admin', 'guard_name' => 'web']);
        $staffRole      = Role::firstOrCreate(['name' => 'Staff', 'guard_name' => 'web']);
        $moderatorRole  = Role::firstOrCreate(['name' => 'Moderator', 'guard_name' => 'web']);
        $studentRole    = Role::firstOrCreate(['name' => 'Student', 'guard_name' => 'web']);

        // Super Admin gets ALL permissions
        $superAdminRole->syncPermissions(Permission::all());

        // Admin gets all except deleting other Super Admins or core system settings
        $adminPermissions = Permission::whereNotIn('name', [
            'delete-users',
        ])->get();
        $adminRole->syncPermissions($allPermissions);

        // Staff: Exams, questions, vendors, blog, media, viewing dashboard & orders
        $staffRole->syncPermissions([
            'view-dashboard',
            'view-vendors', 'create-vendors', 'edit-vendors',
            'view-exams', 'create-exams', 'edit-exams',
            'view-questions', 'create-questions', 'edit-questions', 'import-questions', 'export-questions',
            'view-orders',
            'view-posts', 'create-posts', 'edit-posts',
            'manage-media',
        ]);

        // Moderator: View users, suspend users, manage comments, moderate content
        $moderatorRole->syncPermissions([
            'view-dashboard',
            'view-users', 'suspend-users',
            'view-posts', 'manage-comments',
        ]);

        // Student has normal portal access, no admin permissions
        $studentRole->syncPermissions([]);

        // 3. Assign existing users to roles
        $admins = User::where('role', 'admin')->get();
        foreach ($admins as $index => $adminUser) {
            // First admin user becomes Super Admin
            if ($index === 0 || $adminUser->id === 1) {
                $adminUser->role = 'Super Admin';
                $adminUser->syncRoles(['Super Admin']);
            } else {
                $adminUser->role = 'Admin';
                $adminUser->syncRoles(['Admin']);
            }
            $adminUser->status = 'active';
            $adminUser->save();
        }

        $students = User::where('role', 'student')->get();
        foreach ($students as $studentUser) {
            $studentUser->role = 'Student';
            $studentUser->status = 'active';
            $studentUser->syncRoles(['Student']);
            $studentUser->save();
        }
    }
}
