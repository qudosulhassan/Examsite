<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Exam;
use App\Models\UserExam;
use App\Models\Order;
use App\Models\AuditLog;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Spatie\Permission\Models\Role;

class UserAdminController extends Controller
{
    /**
     * Display users list with statistics, role distribution, filters, search, and sorting.
     */
    public function index(Request $request)
    {
        // 1. Database-driven Real Statistics Cards
        $totalUsers = User::count();
        $activeUsers = User::where('status', 'active')->count();
        $studentUsers = User::whereIn('role', ['student', 'Student'])->count();
        $adminUsers = User::whereIn('role', ['admin', 'Admin', 'Super Admin', 'super_admin'])->count();
        $staffUsers = User::whereIn('role', ['staff', 'Staff', 'moderator', 'Moderator'])->count();
        $suspendedUsers = User::where('status', 'suspended')->count();

        // 2. Role Distribution Data
        $rolesDistribution = Role::withCount('users')->get()->map(function ($role) use ($totalUsers) {
            $percentage = $totalUsers > 0 ? round(($role->users_count / $totalUsers) * 100, 1) : 0;
            return [
                'name' => $role->name,
                'count' => $role->users_count,
                'percentage' => $percentage,
            ];
        });

        // 3. User Query with Filters & Search
        $query = User::with(['roles', 'orders'])->withCount(['orders', 'userExams']);

        // Search (Name, Email, Phone, User ID)
        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
                if (is_numeric($search)) {
                    $q->orWhere('id', (int)$search);
                }
            });
        }

        // Filter: Role
        if ($request->filled('role')) {
            $roleFilter = $request->role;
            $query->where(function ($q) use ($roleFilter) {
                $q->where('role', $roleFilter)
                  ->orWhereHas('roles', function ($rq) use ($roleFilter) {
                      $rq->where('name', $roleFilter);
                  });
            });
        }

        // Filter: Status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter: Email Verified
        if ($request->filled('email_verified')) {
            if ($request->email_verified === 'yes') {
                $query->whereNotNull('email_verified_at');
            } elseif ($request->email_verified === 'no') {
                $query->whereNull('email_verified_at');
            }
        }

        // Filter: Customer / Has Purchases
        if ($request->filled('customer')) {
            if ($request->customer === 'yes') {
                $query->has('orders');
            } elseif ($request->customer === 'no') {
                $query->doesntHave('orders');
            }
        }

        // Filter: Joined Date Range
        if ($request->filled('joined_range')) {
            switch ($request->joined_range) {
                case 'today':
                    $query->whereDate('created_at', today());
                    break;
                case '7days':
                    $query->where('created_at', '>=', now()->subDays(7));
                    break;
                case '30days':
                    $query->where('created_at', '>=', now()->subDays(30));
                    break;
                case 'this_year':
                    $query->whereYear('created_at', now()->year);
                    break;
            }
        }

        // Sorting
        $sortField = $request->get('sort', 'id');
        $sortDirection = strtolower($request->get('direction', 'desc')) === 'asc' ? 'asc' : 'desc';
        $allowedSorts = ['id', 'name', 'email', 'role', 'status', 'created_at', 'last_login_at', 'orders_count'];

        if (in_array($sortField, $allowedSorts)) {
            $query->orderBy($sortField, $sortDirection);
        } else {
            $query->orderBy('id', 'desc');
        }

        // Pagination size
        $perPage = in_array((int)$request->get('per_page'), [25, 50, 100]) ? (int)$request->get('per_page') : 25;
        $users = $query->paginate($perPage)->withQueryString();

        // All Roles for Filter Dropdowns
        $allRoles = Role::orderBy('name')->get();

        return view('admin.users.index', compact(
            'users',
            'totalUsers',
            'activeUsers',
            'studentUsers',
            'adminUsers',
            'staffUsers',
            'suspendedUsers',
            'rolesDistribution',
            'allRoles',
            'sortField',
            'sortDirection',
            'perPage'
        ));
    }

    /**
     * Show form for creating a new user.
     */
    public function create()
    {
        $roles = Role::orderBy('name')->get();
        return view('admin.users.create', compact('roles'));
    }

    /**
     * Store newly created user.
     */
    public function store(Request $request)
    {
        $request->validate([
            'first_name' => 'nullable|string|max:100',
            'last_name' => 'nullable|string|max:100',
            'name' => 'nullable|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'phone' => 'nullable|string|max:50',
            'password' => ['required', 'confirmed', Password::min(8)],
            'role' => 'required|string|exists:roles,name',
            'status' => 'required|in:active,suspended,pending,deactivated',
            'email_verified' => 'nullable|boolean',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        // Construct full name
        $fullName = trim($request->name ?: '');
        if (empty($fullName)) {
            $fullName = trim(($request->first_name ?? '') . ' ' . ($request->last_name ?? ''));
        }
        if (empty($fullName)) {
            $fullName = explode('@', $request->email)[0];
        }

        $avatarPath = null;
        if ($request->hasFile('avatar')) {
            $avatarPath = $request->file('avatar')->store('avatars', 'public');
        }

        $user = User::create([
            'name' => $fullName,
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => strtolower(trim($request->email)),
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'status' => $request->status,
            'avatar' => $avatarPath,
            'email_verified_at' => $request->boolean('email_verified') ? now() : null,
        ]);

        $user->syncRoles([$request->role]);

        // Audit Log
        AuditLogService::log('user_created', "Created user {$user->name} ({$user->email}) with role '{$request->role}' and status '{$request->status}'", $user->id, [
            'name' => $user->name,
            'email' => $user->email,
            'role' => $request->role,
            'status' => $request->status,
        ]);

        return redirect()->route('admin.users.index')->with('success', "User '{$user->name}' created successfully.");
    }

    /**
     * Show comprehensive user profile and activity.
     */
    public function show(int $id)
    {
        $user = User::withTrashed()
            ->with(['roles', 'orders.items', 'userPackages.package'])
            ->findOrFail($id);

        $exams = Exam::orderBy('exam_code')->get();

        $purchasedExams = UserExam::where('user_id', $user->id)
            ->with('exam')
            ->orderBy('id', 'desc')
            ->get();

        $recentOrders = Order::where('user_id', $user->id)
            ->with('items')
            ->orderBy('id', 'desc')
            ->take(10)
            ->get();

        $auditLogs = AuditLog::where('target_user_id', $user->id)
            ->with('admin')
            ->orderBy('id', 'desc')
            ->take(20)
            ->get();

        return view('admin.users.show', compact('user', 'exams', 'purchasedExams', 'recentOrders', 'auditLogs'));
    }

    /**
     * Show form for editing user.
     */
    public function edit(int $id)
    {
        $user = User::withTrashed()->findOrFail($id);
        $roles = Role::orderBy('name')->get();
        return view('admin.users.edit', compact('user', 'roles'));
    }

    /**
     * Update user details.
     */
    public function update(Request $request, int $id)
    {
        $user = User::withTrashed()->findOrFail($id);

        $request->validate([
            'first_name' => 'nullable|string|max:100',
            'last_name' => 'nullable|string|max:100',
            'name' => 'nullable|string|max:255',
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'phone' => 'nullable|string|max:50',
            'password' => ['nullable', 'confirmed', Password::min(8)],
            'role' => 'required|string|exists:roles,name',
            'status' => 'required|in:active,suspended,pending,deactivated',
            'email_verified' => 'nullable|boolean',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        // Prevent downgrading the final Super Admin
        if ($user->isSuperAdmin() && $request->role !== $user->role) {
            $otherSuperAdmins = User::whereIn('role', ['Super Admin', 'super_admin'])
                ->where('id', '!=', $user->id)
                ->count();
            if ($otherSuperAdmins === 0) {
                return back()->with('error', 'Cannot change the role of the final Super Admin account.');
            }
        }

        // Construct full name
        $fullName = trim($request->name ?: '');
        if (empty($fullName)) {
            $fullName = trim(($request->first_name ?? '') . ' ' . ($request->last_name ?? ''));
        }
        if (empty($fullName)) {
            $fullName = $user->name;
        }

        $updateData = [
            'name' => $fullName,
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => strtolower(trim($request->email)),
            'phone' => $request->phone,
            'role' => $request->role,
            'status' => $request->status,
        ];

        // Only update password if provided
        if ($request->filled('password')) {
            $updateData['password'] = Hash::make($request->password);
        }

        // Email verified toggle
        if ($request->has('email_verified')) {
            $updateData['email_verified_at'] = $request->boolean('email_verified') ? ($user->email_verified_at ?: now()) : null;
        }

        // Avatar upload
        if ($request->hasFile('avatar')) {
            if ($user->avatar && !Str::startsWith($user->avatar, ['http', '/'])) {
                Storage::disk('public')->delete($user->avatar);
            }
            $updateData['avatar'] = $request->file('avatar')->store('avatars', 'public');
        }

        $oldRole = $user->role;
        $oldStatus = $user->status;

        $user->update($updateData);
        $user->syncRoles([$request->role]);

        // Audit Log
        AuditLogService::log('user_updated', "Updated user {$user->name} ({$user->email})" . ($oldRole !== $request->role ? " | Role: {$oldRole} -> {$request->role}" : "") . ($oldStatus !== $request->status ? " | Status: {$oldStatus} -> {$request->status}" : ""), $user->id, [
            'old_role' => $oldRole,
            'new_role' => $request->role,
            'old_status' => $oldStatus,
            'new_status' => $request->status,
            'password_changed' => $request->filled('password'),
        ]);

        return redirect()->route('admin.users.index')->with('success', "User '{$user->name}' updated successfully.");
    }

    /**
     * Soft delete user with safety protections.
     */
    public function destroy(int $id)
    {
        $user = User::findOrFail($id);

        // Security check: cannot delete self
        if (auth()->id() === $user->id) {
            return back()->with('error', 'Security Violation: You cannot delete your own active administrator account.');
        }

        // Security check: cannot delete final Super Admin
        if ($user->isSuperAdmin()) {
            $superAdminsCount = User::whereIn('role', ['Super Admin', 'super_admin'])
                ->where('id', '!=', $user->id)
                ->count();
            if ($superAdminsCount === 0) {
                return back()->with('error', 'Security Violation: Cannot delete the only remaining Super Admin account.');
            }
        }

        $userName = $user->name;
        $userEmail = $user->email;
        $userId = $user->id;

        $user->delete(); // Soft delete

        AuditLogService::log('user_deleted', "Soft deleted user {$userName} ({$userEmail})", $userId);

        return redirect()->route('admin.users.index')->with('success', "User '{$userName}' has been soft-deleted successfully.");
    }

    /**
     * Handle bulk actions across selected users.
     */
    public function bulkAction(Request $request)
    {
        $request->validate([
            'action' => 'required|in:activate,suspend,deactivate,assign_role,delete',
            'user_ids' => 'required|array|min:1',
            'user_ids.*' => 'integer|exists:users,id',
            'assign_role' => 'nullable|string|exists:roles,name',
        ]);

        $userIds = $request->user_ids;
        $action = $request->action;
        $currentUserId = auth()->id();

        // Exclude current user from destructive bulk actions
        if (in_array($currentUserId, $userIds) && in_array($action, ['suspend', 'deactivate', 'delete'])) {
            $userIds = array_diff($userIds, [$currentUserId]);
        }

        if (empty($userIds)) {
            return back()->with('error', 'No valid target users selected for this bulk action.');
        }

        $users = User::whereIn('id', $userIds)->get();
        $count = $users->count();

        switch ($action) {
            case 'activate':
                User::whereIn('id', $userIds)->update(['status' => 'active']);
                AuditLogService::log('bulk_activate', "Bulk activated {$count} users", null, ['ids' => $userIds]);
                return back()->with('success', "{$count} users activated successfully.");

            case 'suspend':
                User::whereIn('id', $userIds)->update(['status' => 'suspended']);
                AuditLogService::log('bulk_suspend', "Bulk suspended {$count} users", null, ['ids' => $userIds]);
                return back()->with('success', "{$count} users suspended successfully.");

            case 'deactivate':
                User::whereIn('id', $userIds)->update(['status' => 'deactivated']);
                AuditLogService::log('bulk_deactivate', "Bulk deactivated {$count} users", null, ['ids' => $userIds]);
                return back()->with('success', "{$count} users deactivated successfully.");

            case 'assign_role':
                if (!$request->filled('assign_role')) {
                    return back()->with('error', 'Please specify a target role to assign.');
                }
                $targetRole = $request->assign_role;
                foreach ($users as $u) {
                    $u->role = $targetRole;
                    $u->save();
                    $u->syncRoles([$targetRole]);
                }
                AuditLogService::log('bulk_assign_role', "Bulk assigned role '{$targetRole}' to {$count} users", null, ['ids' => $userIds, 'role' => $targetRole]);
                return back()->with('success', "Assigned role '{$targetRole}' to {$count} users.");

            case 'delete':
                foreach ($users as $u) {
                    if ($u->isSuperAdmin()) {
                        continue; // Skip deleting super admins in bulk
                    }
                    $u->delete();
                }
                AuditLogService::log('bulk_delete', "Bulk soft-deleted {$count} users", null, ['ids' => $userIds]);
                return back()->with('success', "Bulk delete completed for selected users.");
        }

        return back();
    }

    /**
     * Export users matching current filters as CSV.
     */
    public function export(Request $request)
    {
        $query = User::query();

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $users = $query->orderBy('id', 'desc')->get();

        AuditLogService::log('users_exported', "Exported {$users->count()} users to CSV");

        $filename = 'users_export_' . now()->format('Y_m_d_His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $callback = function () use ($users) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['User ID', 'Name', 'Email', 'Phone', 'Role', 'Status', 'Email Verified', 'Last Login', 'Created At']);

            foreach ($users as $u) {
                fputcsv($file, [
                    $u->id,
                    $u->name,
                    $u->email,
                    $u->phone ?? 'N/A',
                    $u->role,
                    strtoupper($u->status ?? 'active'),
                    $u->email_verified_at ? 'Yes' : 'No',
                    $u->last_login_at ? $u->last_login_at->format('Y-m-d H:i') : 'Never',
                    $u->created_at ? $u->created_at->format('Y-m-d H:i') : 'N/A',
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Manually grant certification access.
     */
    public function grantAccess(Request $request, int $userId)
    {
        $request->validate([
            'exam_id' => 'required|exists:exams,id',
            'access_type' => 'required|in:pdf,engine',
        ]);

        $exists = UserExam::where('user_id', $userId)
            ->where('exam_id', $request->exam_id)
            ->where('access_type', $request->access_type)
            ->exists();

        if ($exists) {
            return back()->with('error', 'User already has this access level granted.');
        }

        $exam = Exam::find($request->exam_id);

        UserExam::create([
            'user_id' => $userId,
            'exam_id' => $request->exam_id,
            'access_type' => $request->access_type,
            'purchased_at' => now(),
            'max_downloads' => ($request->access_type === 'pdf') ? 3 : 0,
        ]);

        AuditLogService::log('access_granted', "Granted {$request->access_type} access for {$exam->exam_code} to user ID {$userId}", $userId);

        return back()->with('success', 'Exam access manual grant succeeded.');
    }

    /**
     * Revoke manual certification access.
     */
    public function revokeAccess(Request $request, int $userId)
    {
        $request->validate([
            'user_exam_id' => 'required|exists:user_exams,id',
        ]);

        $userExam = UserExam::where('id', $request->user_exam_id)
            ->where('user_id', $userId)
            ->firstOrFail();

        $examCode = $userExam->exam ? $userExam->exam->exam_code : 'N/A';
        $userExam->delete();

        AuditLogService::log('access_revoked', "Revoked exam access for {$examCode} from user ID {$userId}", $userId);

        return back()->with('success', 'Exam access revoked successfully.');
    }
}
