@extends('layouts.admin')

@section('content')
<div class="space-y-6">
    <!-- Breadcrumb & Top Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2 text-xs font-semibold text-gray-400 mb-1">
                <a href="{{ route('admin.users.index') }}" class="hover:text-navy transition">Users</a>
                <span>/</span>
                <span class="text-navy font-bold">Security Audit Logs</span>
            </div>
            <h1 class="text-2xl font-black text-navy tracking-tight">Administrative Activity Logs</h1>
            <p class="text-xs text-gray-500 mt-1">Immutable audit trail of administrator actions, status changes, privilege assignments, and security events.</p>
        </div>
        <div class="flex items-center space-x-3">
            <a href="{{ route('admin.users.index') }}" class="inline-flex items-center space-x-2 text-xs font-bold text-gray-600 bg-white border border-gray-250 px-4 py-2.5 rounded-lg hover:bg-gray-50 transition shadow-sm">
                <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                <span>Users Listing</span>
            </a>
        </div>
    </div>

    <!-- Filter & Search Toolbar -->
    <div class="bg-white border border-gray-250 rounded-xl p-5 shadow-sm">
        <form action="{{ route('admin.audit-logs.index') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-5 gap-4 items-end">
            <!-- Search Keyword -->
            <div class="md:col-span-2">
                <label for="search" class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1.5">Search Description / IP</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    </div>
                    <input type="text" name="search" id="search" value="{{ request('search') }}" placeholder="Search action, description, or IP..."
                           class="w-full pl-9 text-xs border-gray-250 rounded-lg px-3 py-2.5 focus:border-cyan focus:ring-cyan transition">
                </div>
            </div>

            <!-- Action Type -->
            <div>
                <label for="action" class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1.5">Action Type</label>
                <select name="action" id="action" class="w-full text-xs border-gray-250 rounded-lg px-3 py-2.5 focus:border-cyan focus:ring-cyan bg-white">
                    <option value="">All Actions</option>
                    @foreach($actions as $act)
                        <option value="{{ $act }}" {{ request('action') === $act ? 'selected' : '' }}>{{ $act }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Admin Filter -->
            <div>
                <label for="admin_id" class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1.5">Administrator</label>
                <select name="admin_id" id="admin_id" class="w-full text-xs border-gray-250 rounded-lg px-3 py-2.5 focus:border-cyan focus:ring-cyan bg-white">
                    <option value="">All Admins</option>
                    @foreach($admins as $adm)
                        <option value="{{ $adm->id }}" {{ request('admin_id') == $adm->id ? 'selected' : '' }}>{{ $adm->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Buttons -->
            <div class="flex items-center space-x-2">
                <button type="submit" class="w-full bg-navy text-white text-xs font-bold py-2.5 px-4 rounded-lg shadow-sm hover:bg-opacity-95 transition flex items-center justify-center space-x-1.5">
                    <svg class="w-3.5 h-3.5 text-cyan" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path></svg>
                    <span>Filter</span>
                </button>
                @if(request()->hasAny(['search', 'action', 'admin_id', 'date_from', 'date_to']))
                    <a href="{{ route('admin.audit-logs.index') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-600 text-xs font-bold py-2.5 px-3 rounded-lg transition" title="Reset Filters">
                        ✕
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Logs Table -->
    <div class="bg-white border border-gray-250 rounded-xl shadow-sm overflow-hidden" x-data="{
        selectedPayload: null,
        openModal(payload) {
            this.selectedPayload = payload;
        },
        closeModal() {
            this.selectedPayload = null;
        }
    }">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-200 text-gray-400 font-bold uppercase text-[10px] tracking-wider">
                        <th class="py-3.5 px-6">Timestamp</th>
                        <th class="py-3.5 px-6">Administrator</th>
                        <th class="py-3.5 px-6">Event Action</th>
                        <th class="py-3.5 px-6">Description</th>
                        <th class="py-3.5 px-6">Target User</th>
                        <th class="py-3.5 px-6">IP / Agent</th>
                        <th class="py-3.5 px-6 text-right">Details</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-150 font-medium">
                    @forelse($logs as $log)
                        <tr class="hover:bg-gray-50/80 transition">
                            <!-- Timestamp -->
                            <td class="py-3.5 px-6 whitespace-nowrap">
                                <span class="font-bold text-navy block">{{ $log->created_at->format('M d, Y') }}</span>
                                <span class="text-[10px] text-gray-400 font-mono">{{ $log->created_at->format('H:i:s') }} ({{ $log->created_at->diffForHumans() }})</span>
                            </td>

                            <!-- Admin -->
                            <td class="py-3.5 px-6 whitespace-nowrap">
                                @if($log->admin)
                                    <div class="flex items-center space-x-2">
                                        <div class="w-6 h-6 rounded-full bg-navy text-cyan text-[10px] font-black flex items-center justify-center">
                                            {{ $log->admin->initials }}
                                        </div>
                                        <span class="font-bold text-navy">{{ $log->admin->name }}</span>
                                    </div>
                                @else
                                    <span class="text-gray-400 italic">System Automation</span>
                                @endif
                            </td>

                            <!-- Action Badge -->
                            <td class="py-3.5 px-6 whitespace-nowrap">
                                <span class="px-2.5 py-1 rounded text-[10px] font-black font-mono uppercase tracking-wider
                                    @if(Str::contains($log->action, 'delete') || Str::contains($log->action, 'revoke')) bg-red-50 text-red-700 border border-red-200
                                    @elseif(Str::contains($log->action, 'create')) bg-emerald-50 text-emerald-700 border border-emerald-200
                                    @elseif(Str::contains($log->action, 'update') || Str::contains($log->action, 'grant')) bg-cyan bg-opacity-20 text-navy border border-cyan border-opacity-30
                                    @else bg-gray-100 text-gray-700 border border-gray-200
                                    @endif">
                                    {{ $log->action }}
                                </span>
                            </td>

                            <!-- Description -->
                            <td class="py-3.5 px-6 text-gray-700 max-w-md break-words">
                                {{ $log->description }}
                            </td>

                            <!-- Target User -->
                            <td class="py-3.5 px-6 whitespace-nowrap">
                                @if($log->targetUser)
                                    <a href="{{ route('admin.users.show', $log->targetUser->id) }}" class="font-bold text-navy hover:text-cyan transition flex items-center space-x-1.5">
                                        <span>{{ $log->targetUser->name }}</span>
                                        <span class="text-[10px] text-gray-400 font-normal">#{{ $log->targetUser->id }}</span>
                                    </a>
                                @elseif($log->target_user_id)
                                    <span class="text-gray-400 font-mono text-[11px]">User #{{ $log->target_user_id }} (Deleted)</span>
                                @else
                                    <span class="text-gray-400">—</span>
                                @endif
                            </td>

                            <!-- IP Address -->
                            <td class="py-3.5 px-6 whitespace-nowrap font-mono text-[11px] text-gray-500">
                                <div>{{ $log->ip_address ?: '127.0.0.1' }}</div>
                            </td>

                            <!-- Payload Detail -->
                            <td class="py-3.5 px-6 text-right whitespace-nowrap">
                                @if(!empty($log->payload))
                                    <button type="button" @click='openModal(@json($log->payload))' class="text-xs font-bold text-navy hover:text-cyan bg-gray-100 hover:bg-gray-200 px-2.5 py-1 rounded transition">
                                        View Data
                                    </button>
                                @else
                                    <span class="text-gray-300">—</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-12 text-center text-gray-400">
                                <svg class="w-10 h-10 mx-auto text-gray-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                <p class="text-xs font-bold text-gray-500">No audit log records found matching the query.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($logs->hasPages())
            <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
                {{ $logs->links() }}
            </div>
        @endif

        <!-- JSON Payload Modal -->
        <div x-show="selectedPayload" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 p-4" @keydown.escape.window="closeModal()">
            <div class="bg-white rounded-xl shadow-2xl max-w-lg w-full p-6 space-y-4" @click.away="closeModal()">
                <div class="flex items-center justify-between border-b border-gray-150 pb-3">
                    <h3 class="text-sm font-black text-navy uppercase tracking-wider">Payload Changes / Audit Metadata</h3>
                    <button type="button" @click="closeModal()" class="text-gray-400 hover:text-navy text-lg font-bold">✕</button>
                </div>
                <div class="bg-gray-900 text-green-400 font-mono text-xs p-4 rounded-lg overflow-x-auto max-h-80">
                    <pre x-text="JSON.stringify(selectedPayload, null, 2)"></pre>
                </div>
                <div class="flex justify-end pt-2">
                    <button type="button" @click="closeModal()" class="px-4 py-2 text-xs font-bold text-navy bg-gray-100 hover:bg-gray-200 rounded-lg transition">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
