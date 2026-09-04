@extends('layouts.admin')

@section('content')
<div class="space-y-6">
    <!-- Breadcrumb & Top Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2 text-xs font-semibold text-gray-400 mb-1">
                <a href="{{ route('admin.users.index') }}" class="hover:text-navy transition">Users</a>
                <span>/</span>
                <span class="text-navy font-bold">Roles & Permissions</span>
            </div>
            <h1 class="text-2xl font-black text-navy tracking-tight">System Roles & RBAC Matrix</h1>
            <p class="text-xs text-gray-500 mt-1">Configure role levels, capability boundaries, and assign granular permissions across the application.</p>
        </div>
        <div class="flex items-center space-x-3">
            <a href="{{ route('admin.users.index') }}" class="inline-flex items-center space-x-2 text-xs font-bold text-gray-600 bg-white border border-gray-250 px-4 py-2.5 rounded-lg hover:bg-gray-50 transition shadow-sm">
                <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                <span>Users Listing</span>
            </a>
            <a href="{{ route('admin.roles.create') }}" class="inline-flex items-center space-x-2 text-xs font-bold text-navy bg-cyan px-4 py-2.5 rounded-lg shadow-sm hover:brightness-105 transition">
                <svg class="w-4 h-4 text-navy" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                <span>Create New Role</span>
            </a>
        </div>
    </div>

    <!-- Alert Notifications -->
    @if(session('success'))
        <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl text-xs font-bold flex items-center space-x-2 shadow-sm">
            <svg class="w-4 h-4 text-emerald-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif
    @if(session('error'))
        <div class="p-4 bg-red-50 border border-red-200 text-red-800 rounded-xl text-xs font-bold flex items-center space-x-2 shadow-sm">
            <svg class="w-4 h-4 text-red-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <!-- Roles Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach($roles as $role)
            <div class="bg-white border border-gray-250 rounded-xl p-6 shadow-sm hover:shadow-md transition flex flex-col justify-between space-y-5">
                <div>
                    <!-- Header -->
                    <div class="flex items-start justify-between">
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 rounded-xl flex items-center justify-center font-black text-sm
                                @if(in_array($role->name, ['Super Admin', 'super_admin'])) bg-purple-100 text-purple-700 border border-purple-200
                                @elseif(in_array($role->name, ['Admin', 'admin'])) bg-navy text-cyan
                                @elseif($role->name === 'Staff') bg-blue-100 text-blue-700 border border-blue-200
                                @elseif($role->name === 'Moderator') bg-amber-100 text-amber-700 border border-amber-200
                                @else bg-gray-100 text-gray-700 border border-gray-200
                                @endif">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                            </div>
                            <div>
                                <h3 class="text-base font-black text-navy">{{ $role->name }}</h3>
                                <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Guard: {{ $role->guard_name }}</span>
                            </div>
                        </div>

                        @if(in_array(strtolower($role->name), ['super admin', 'admin', 'student']))
                            <span class="px-2 py-0.5 rounded text-[10px] font-black uppercase tracking-wider bg-gray-100 text-gray-500 border border-gray-200">
                                System
                            </span>
                        @else
                            <span class="px-2 py-0.5 rounded text-[10px] font-black uppercase tracking-wider bg-cyan bg-opacity-15 text-navy border border-cyan border-opacity-30">
                                Custom
                            </span>
                        @endif
                    </div>

                    <!-- Meta Counts -->
                    <div class="grid grid-cols-2 gap-3 mt-5 pt-4 border-t border-gray-150">
                        <div class="bg-gray-50 rounded-lg p-2.5">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-gray-400 block">Assigned Users</span>
                            <span class="text-lg font-black text-navy">{{ $role->users_count }}</span>
                        </div>
                        <div class="bg-gray-50 rounded-lg p-2.5">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-gray-400 block">Permissions</span>
                            <span class="text-lg font-black text-cyan">{{ $role->permissions->count() }}</span>
                        </div>
                    </div>

                    <!-- Sample Permissions Badges -->
                    <div class="mt-4">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-gray-400 block mb-2">Granted Capabilities</span>
                        <div class="flex flex-wrap gap-1.5 max-h-24 overflow-y-auto">
                            @forelse($role->permissions->take(6) as $perm)
                                <span class="px-2 py-0.5 rounded text-[10px] font-medium bg-gray-100 text-gray-600 font-mono">
                                    {{ $perm->name }}
                                </span>
                            @empty
                                <span class="text-xs text-gray-400 italic">No specific permissions granted yet.</span>
                            @endforelse
                            @if($role->permissions->count() > 6)
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-gray-200 text-gray-700">
                                    +{{ $role->permissions->count() - 6 }} more
                                </span>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Footer Actions -->
                <div class="border-t border-gray-150 pt-4 flex items-center justify-between">
                    <a href="{{ route('admin.roles.edit', $role->id) }}" class="text-xs font-bold text-navy hover:text-cyan flex items-center space-x-1.5 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                        <span>Edit Permissions</span>
                    </a>

                    @if(!in_array(strtolower($role->name), ['super admin', 'admin', 'student']))
                        <form action="{{ route('admin.roles.destroy', $role->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to permanently delete role \'{{ $role->name }}\'?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-xs font-bold text-red-500 hover:text-red-700 transition">
                                Delete
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
</div>
@endsection
