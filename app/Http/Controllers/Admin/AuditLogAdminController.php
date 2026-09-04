<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;

class AuditLogAdminController extends Controller
{
    /**
     * Display searchable, filterable audit logs.
     */
    public function index(Request $request)
    {
        $query = AuditLog::with(['admin', 'targetUser']);

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('action', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('ip_address', 'like', "%{$search}%");
            });
        }

        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        if ($request->filled('admin_id')) {
            $query->where('admin_id', $request->admin_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $logs = $query->orderBy('id', 'desc')->paginate(25)->withQueryString();

        $actions = AuditLog::distinct()->pluck('action');
        $admins = User::whereIn('role', ['admin', 'Admin', 'Super Admin', 'super_admin'])->get();

        return view('admin.audit-logs.index', compact('logs', 'actions', 'admins'));
    }
}
