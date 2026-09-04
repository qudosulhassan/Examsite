@extends('layouts.admin')

@section('content')
<div class="max-w-5xl mx-auto space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2 text-xs font-semibold text-gray-400 mb-1">
                <a href="{{ route('admin.users.index') }}" class="hover:text-navy transition">Users</a>
                <span>/</span>
                <a href="{{ route('admin.roles.index') }}" class="hover:text-navy transition">Roles</a>
                <span>/</span>
                <span class="text-navy font-bold">Edit {{ $role->name }}</span>
            </div>
            <div class="flex items-center space-x-3">
                <h1 class="text-2xl font-black text-navy tracking-tight">Edit Role: {{ $role->name }}</h1>
                @if(in_array(strtolower($role->name), ['super admin', 'admin', 'student']))
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-gray-100 text-gray-600 border border-gray-200">
                        System Core Role
                    </span>
                @endif
            </div>
            <p class="text-xs text-gray-500 mt-1">Update role details and adjust assigned module permissions.</p>
        </div>
        <a href="{{ route('admin.roles.index') }}" class="inline-flex items-center space-x-2 text-xs font-bold text-gray-600 bg-white border border-gray-250 px-4 py-2.5 rounded-lg hover:bg-gray-50 transition shadow-sm">
            <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            <span>Back to Roles</span>
        </a>
    </div>

    <!-- Alert Notifications -->
    @if(session('error'))
        <div class="p-4 bg-red-50 border border-red-200 text-red-800 rounded-xl text-xs font-bold flex items-center space-x-2 shadow-sm">
            <svg class="w-4 h-4 text-red-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <!-- Main Card -->
    <div class="bg-white border border-gray-250 rounded-xl shadow-sm overflow-hidden" x-data="{
        toggleGroup(group) {
            let boxes = document.querySelectorAll('.' + group);
            let anyUnchecked = Array.from(boxes).some(b => !b.checked);
            boxes.forEach(b => b.checked = anyUnchecked);
        },
        toggleAll(state) {
            document.querySelectorAll('.permission-check').forEach(b => b.checked = state);
        }
    }">
        <form action="{{ route('admin.roles.update', $role->id) }}" method="POST" class="p-6 sm:p-8 space-y-8">
            @csrf
            @method('PUT')

            <!-- Role Name -->
            <div class="max-w-md">
                <label for="name" class="block text-xs font-bold text-navy uppercase tracking-wider mb-2">Role Name <span class="text-red-500">*</span></label>
                <input type="text" name="name" id="name" required value="{{ old('name', $role->name) }}"
                       @if(in_array(strtolower($role->name), ['super admin', 'student'])) readonly @endif
                       class="w-full text-sm font-semibold border-gray-250 rounded-lg px-3.5 py-2.5 focus:border-cyan focus:ring-cyan transition @if(in_array(strtolower($role->name), ['super admin', 'student'])) bg-gray-50 cursor-not-allowed @endif">
                @error('name')
                    <p class="text-red-500 text-xs mt-1 font-semibold">{{ $message }}</p>
                @enderror
                @if(in_array(strtolower($role->name), ['super admin', 'student']))
                    <p class="text-[11px] text-gray-400 mt-1.5">Core system roles cannot be renamed.</p>
                @endif
            </div>

            <!-- Permission Matrix Header -->
            <div class="border-t border-gray-200 pt-6">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-6">
                    <div>
                        <h2 class="text-base font-black text-navy">Module Permissions Matrix</h2>
                        <p class="text-xs text-gray-500">Currently has <strong class="text-cyan">{{ count($rolePermissions) }}</strong> capabilities granted.</p>
                    </div>
                    <div class="flex items-center space-x-2">
                        <button type="button" @click="toggleAll(true)" class="text-xs font-bold text-navy bg-gray-100 hover:bg-gray-200 px-3 py-1.5 rounded-md transition">
                            Select All
                        </button>
                        <button type="button" @click="toggleAll(false)" class="text-xs font-bold text-gray-600 bg-gray-100 hover:bg-gray-200 px-3 py-1.5 rounded-md transition">
                            Deselect All
                        </button>
                    </div>
                </div>

                <!-- Grouped Permissions -->
                <div class="space-y-6">
                    @foreach($groupedPermissions as $groupName => $permissions)
                        @php $slug = Str::slug($groupName); @endphp
                        <div class="border border-gray-200 rounded-xl overflow-hidden bg-gray-50/50">
                            <!-- Group Header -->
                            <div class="bg-gray-50 px-5 py-3 border-b border-gray-200 flex items-center justify-between">
                                <div class="flex items-center space-x-2">
                                    <span class="w-2 h-2 rounded-full bg-cyan"></span>
                                    <h3 class="text-xs font-black text-navy uppercase tracking-wider">{{ $groupName }}</h3>
                                    <span class="text-[10px] font-bold text-gray-400">({{ count($permissions) }})</span>
                                </div>
                                <button type="button" @click="toggleGroup('group-{{ $slug }}')" class="text-[11px] font-bold text-navy hover:text-cyan transition">
                                    Toggle Group
                                </button>
                            </div>

                            <!-- Group Checkboxes -->
                            <div class="p-5 grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3 bg-white">
                                @foreach($permissions as $permission)
                                    <label class="flex items-start space-x-2.5 p-2 rounded-lg hover:bg-gray-50 transition cursor-pointer select-none">
                                        <input type="checkbox" name="permissions[]" value="{{ $permission->name }}"
                                               class="permission-check group-{{ $slug }} w-4 h-4 text-cyan border-gray-300 rounded focus:ring-cyan mt-0.5"
                                               {{ (is_array(old('permissions')) ? in_array($permission->name, old('permissions')) : in_array($permission->name, $rolePermissions)) ? 'checked' : '' }}>
                                        <div class="text-xs">
                                            <span class="font-bold text-navy font-mono text-[11px] block">{{ $permission->name }}</span>
                                            <span class="text-[10px] text-gray-400 capitalize">{{ str_replace(['-', '_'], ' ', $permission->name) }}</span>
                                        </div>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="border-t border-gray-200 pt-6 flex items-center justify-end space-x-4">
                <a href="{{ route('admin.roles.index') }}" class="px-5 py-2.5 text-xs font-bold text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-lg transition">
                    Cancel
                </a>
                <button type="submit" class="inline-flex items-center space-x-2 px-6 py-2.5 bg-navy hover:bg-opacity-95 text-white text-xs font-bold rounded-lg shadow-sm hover:shadow transition">
                    <svg class="w-4 h-4 text-cyan" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    <span>Update Role Permissions</span>
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
