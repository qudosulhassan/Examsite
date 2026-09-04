<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionController extends Controller
{
    /**
     * Group permissions systematically for UI rendering.
     */
    protected function getGroupedPermissions()
    {
        $all = Permission::orderBy('name')->get();
        $groups = [];

        foreach ($all as $permission) {
            $parts = explode('-', $permission->name);
            $action = $parts[0] ?? 'manage';
            $module = count($parts) > 1 ? ucfirst(end($parts)) : 'General';

            // Custom humanized groupings
            if (Str::contains($permission->name, 'dashboard')) $module = 'Dashboard';
            elseif (Str::contains($permission->name, 'user') || Str::contains($permission->name, 'role') || Str::contains($permission->name, 'permission') || Str::contains($permission->name, 'audit')) $module = 'Users & Security';
            elseif (Str::contains($permission->name, 'vendor')) $module = 'Vendors';
            elseif (Str::contains($permission->name, 'exam')) $module = 'Exams';
            elseif (Str::contains($permission->name, 'question')) $module = 'Questions';
            elseif (Str::contains($permission->name, 'order')) $module = 'Orders & Revenue';
            elseif (Str::contains($permission->name, 'subscription')) $module = 'Subscriptions';
            elseif (Str::contains($permission->name, 'coupon')) $module = 'Coupons';
            elseif (Str::contains($permission->name, 'post') || Str::contains($permission->name, 'comment') || Str::contains($permission->name, 'subscriber')) $module = 'Blog & Content';
            elseif (Str::contains($permission->name, 'media')) $module = 'Media';
            elseif (Str::contains($permission->name, 'setting')) $module = 'Settings';
            elseif (Str::contains($permission->name, 'report')) $module = 'Reports';

            $groups[$module][] = $permission;
        }

        ksort($groups);
        return $groups;
    }

    /**
     * Display listing of roles with user counts and permission summaries.
     */
    public function index()
    {
        $roles = Role::with(['permissions'])->withCount('users')->get();
        return view('admin.roles.index', compact('roles'));
    }

    /**
     * Show form to create a new role with selectable permissions.
     */
    public function create()
    {
        $groupedPermissions = $this->getGroupedPermissions();
        return view('admin.roles.create', compact('groupedPermissions'));
    }

    /**
     * Store a newly created role.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100|unique:roles,name',
            'description' => 'nullable|string|max:255',
            'permissions' => 'nullable|array',
            'permissions.*' => 'string|exists:permissions,name',
        ]);

        $role = Role::create([
            'name' => trim($request->name),
            'guard_name' => 'web',
        ]);

        if ($request->has('permissions')) {
            $role->syncPermissions($request->permissions);
        }

        AuditLogService::log('role_created', "Created role '{$role->name}' with " . count($request->permissions ?? []) . " permissions", null, [
            'role_name' => $role->name,
            'permissions_count' => count($request->permissions ?? []),
        ]);

        return redirect()->route('admin.roles.index')->with('success', "Role '{$role->name}' created successfully.");
    }

    /**
     * Show form for editing role and its permissions.
     */
    public function edit(int $id)
    {
        $role = Role::with('permissions')->findOrFail($id);
        $groupedPermissions = $this->getGroupedPermissions();
        $rolePermissions = $role->permissions->pluck('name')->toArray();

        return view('admin.roles.edit', compact('role', 'groupedPermissions', 'rolePermissions'));
    }

    /**
     * Update an existing role.
     */
    public function update(Request $request, int $id)
    {
        $role = Role::findOrFail($id);

        $request->validate([
            'name' => ['required', 'string', 'max:100', Rule::unique('roles', 'name')->ignore($role->id)],
            'description' => 'nullable|string|max:255',
            'permissions' => 'nullable|array',
            'permissions.*' => 'string|exists:permissions,name',
        ]);

        // Core system roles cannot be renamed
        if (in_array(strtolower($role->name), ['super admin', 'student']) && strtolower($role->name) !== strtolower($request->name)) {
            return back()->with('error', "Core system role '{$role->name}' cannot be renamed.");
        }

        $oldName = $role->name;
        $role->name = trim($request->name);
        $role->save();

        if ($request->has('permissions')) {
            $role->syncPermissions($request->permissions);
        } else {
            $role->syncPermissions([]);
        }

        AuditLogService::log('role_updated', "Updated role '{$role->name}' permissions", null, [
            'old_name' => $oldName,
            'new_name' => $role->name,
            'permissions_count' => count($request->permissions ?? []),
        ]);

        return redirect()->route('admin.roles.index')->with('success', "Role '{$role->name}' updated successfully.");
    }

    /**
     * Delete a role with system protection.
     */
    public function destroy(int $id)
    {
        $role = Role::withCount('users')->findOrFail($id);

        // Core system roles cannot be deleted
        if (in_array(strtolower($role->name), ['super admin', 'admin', 'student'])) {
            return back()->with('error', "Protected core role '{$role->name}' cannot be deleted.");
        }

        if ($role->users_count > 0) {
            return back()->with('error', "Cannot delete role '{$role->name}' because {$role->users_count} users are currently assigned to it. Please reassign them first.");
        }

        $roleName = $role->name;
        $role->delete();

        AuditLogService::log('role_deleted', "Deleted custom role '{$roleName}'");

        return redirect()->route('admin.roles.index')->with('success', "Role '{$roleName}' deleted successfully.");
    }
}
