@extends('layouts.admin')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2 text-xs font-semibold text-gray-400 mb-1">
                <a href="{{ route('admin.users.index') }}" class="hover:text-navy transition">Users</a>
                <span>/</span>
                <span class="text-navy font-bold">Edit User #{{ $user->id }}</span>
            </div>
            <div class="flex items-center space-x-3">
                <h1 class="text-2xl font-black text-navy tracking-tight">Edit User Account</h1>
                @if($user->status === 'active')
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-green-50 text-green-700 border border-green-200">Active</span>
                @elseif($user->status === 'suspended')
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-red-50 text-red-700 border border-red-200">Suspended</span>
                @elseif($user->status === 'pending')
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200">Pending</span>
                @else
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-gray-100 text-gray-700 border border-gray-200">Deactivated</span>
                @endif
            </div>
            <p class="text-xs text-gray-500 mt-1">Update profile information, security credentials, system permissions, and status.</p>
        </div>
        <div class="flex items-center space-x-3">
            <a href="{{ route('admin.users.show', $user->id) }}" class="inline-flex items-center space-x-2 text-xs font-bold text-navy bg-cyan bg-opacity-15 border border-cyan border-opacity-30 px-4 py-2.5 rounded-lg hover:bg-opacity-25 transition shadow-sm">
                <svg class="w-4 h-4 text-navy" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                <span>View Full Profile</span>
            </a>
            <a href="{{ route('admin.users.index') }}" class="inline-flex items-center space-x-2 text-xs font-bold text-gray-600 bg-white border border-gray-250 px-4 py-2.5 rounded-lg hover:bg-gray-50 transition shadow-sm">
                <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                <span>Back</span>
            </a>
        </div>
    </div>

    <!-- Main Card -->
    <div class="bg-white border border-gray-250 rounded-xl shadow-sm overflow-hidden">
        <!-- Banner Profile Info -->
        <div class="p-6 border-b border-gray-150 bg-gradient-to-r from-gray-50 via-white to-gray-50 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div class="flex items-center space-x-4">
                <div class="relative">
                    @if($user->avatar)
                        <img src="{{ Str::startsWith($user->avatar, ['http', '/']) ? $user->avatar : asset('storage/' . $user->avatar) }}"
                             alt="{{ $user->name }}" class="w-14 h-14 rounded-xl object-cover border-2 border-white shadow-sm ring-1 ring-gray-200">
                    @else
                        <div class="w-14 h-14 rounded-xl bg-navy text-cyan font-black text-lg flex items-center justify-center border-2 border-white shadow-sm ring-1 ring-gray-200">
                            {{ $user->initials }}
                        </div>
                    @endif
                </div>
                <div>
                    <h2 class="text-lg font-black text-navy">{{ $user->name }}</h2>
                    <div class="flex flex-wrap items-center gap-2 text-xs text-gray-500 mt-0.5">
                        <span class="font-medium">{{ $user->email }}</span>
                        <span>•</span>
                        <span>Joined {{ $user->created_at ? $user->created_at->format('M d, Y') : 'Unknown' }}</span>
                        @if($user->last_login_at)
                            <span>•</span>
                            <span class="text-cyan font-bold">Last active {{ $user->last_login_at->diffForHumans() }}</span>
                        @endif
                    </div>
                </div>
            </div>
            
            <div class="flex items-center space-x-2 text-xs font-semibold text-gray-500 bg-white border border-gray-200 px-3 py-1.5 rounded-lg shadow-2xs">
                <span>Orders: <strong class="text-navy">{{ $user->orders()->count() }}</strong></span>
                <span>•</span>
                <span>User ID: <strong class="text-navy">#{{ $user->id }}</strong></span>
            </div>
        </div>

        <form action="{{ route('admin.users.update', $user->id) }}" method="POST" enctype="multipart/form-data" class="p-6 sm:p-8 space-y-6">
            @csrf
            @method('PUT')

            <!-- Name Fields -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label for="first_name" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">First Name</label>
                    <input type="text" name="first_name" id="first_name" value="{{ old('first_name', $user->first_name) }}" placeholder="e.g. Alex"
                           class="w-full text-sm border-gray-250 rounded-lg px-3.5 py-2.5 focus:border-cyan focus:ring-cyan transition">
                    @error('first_name')
                        <p class="text-red-500 text-xs mt-1 font-semibold">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="last_name" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Last Name</label>
                    <input type="text" name="last_name" id="last_name" value="{{ old('last_name', $user->last_name) }}" placeholder="e.g. Mercer"
                           class="w-full text-sm border-gray-250 rounded-lg px-3.5 py-2.5 focus:border-cyan focus:ring-cyan transition">
                    @error('last_name')
                        <p class="text-red-500 text-xs mt-1 font-semibold">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Email & Phone -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label for="email" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Email Address <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"></path></svg>
                        </div>
                        <input type="email" name="email" id="email" required value="{{ old('email', $user->email) }}"
                               class="w-full pl-10 text-sm border-gray-250 rounded-lg px-3.5 py-2.5 focus:border-cyan focus:ring-cyan transition">
                    </div>
                    @error('email')
                        <p class="text-red-500 text-xs mt-1 font-semibold">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="phone" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Phone Number</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path></svg>
                        </div>
                        <input type="text" name="phone" id="phone" value="{{ old('phone', $user->phone) }}" placeholder="+1 (555) 000-0000"
                               class="w-full pl-10 text-sm border-gray-250 rounded-lg px-3.5 py-2.5 focus:border-cyan focus:ring-cyan transition">
                    </div>
                    @error('phone')
                        <p class="text-red-500 text-xs mt-1 font-semibold">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Role & Status Section -->
            <div class="p-5 rounded-xl bg-gray-50 border border-gray-200 grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label for="role" class="block text-xs font-bold text-navy uppercase tracking-wider mb-2">Assigned System Role <span class="text-red-500">*</span></label>
                    <select name="role" id="role" required class="w-full text-sm font-semibold border-gray-300 rounded-lg px-3.5 py-2.5 focus:border-cyan focus:ring-cyan bg-white">
                        @foreach($roles as $role)
                            <option value="{{ $role->name }}" {{ old('role', $user->role) === $role->name ? 'selected' : '' }}>
                                {{ $role->name }} ({{ $role->permissions->count() }} permissions)
                            </option>
                        @endforeach
                    </select>
                    @if($user->isSuperAdmin())
                        <p class="text-[11px] text-amber-600 font-semibold mt-1.5 flex items-center space-x-1">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                            <span>Super Admin account. System ensures at least one Super Admin remains.</span>
                        </p>
                    @else
                        <p class="text-[11px] text-gray-500 mt-1.5">Determines administrative capabilities and menu accessibility.</p>
                    @endif
                    @error('role')
                        <p class="text-red-500 text-xs mt-1 font-semibold">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="status" class="block text-xs font-bold text-navy uppercase tracking-wider mb-2">Account Status <span class="text-red-500">*</span></label>
                    <select name="status" id="status" required class="w-full text-sm font-semibold border-gray-300 rounded-lg px-3.5 py-2.5 focus:border-cyan focus:ring-cyan bg-white">
                        <option value="active" {{ old('status', $user->status ?? 'active') === 'active' ? 'selected' : '' }}>Active (Full Access)</option>
                        <option value="suspended" {{ old('status', $user->status) === 'suspended' ? 'selected' : '' }}>Suspended (Login Blocked)</option>
                        <option value="pending" {{ old('status', $user->status) === 'pending' ? 'selected' : '' }}>Pending Verification</option>
                        <option value="deactivated" {{ old('status', $user->status) === 'deactivated' ? 'selected' : '' }}>Deactivated</option>
                    </select>
                    <p class="text-[11px] text-gray-500 mt-1.5">Suspended or deactivated accounts cannot sign in or download materials.</p>
                    @error('status')
                        <p class="text-red-500 text-xs mt-1 font-semibold">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Optional Password Reset -->
            <div class="border border-gray-200 rounded-xl p-5 bg-white space-y-4">
                <div class="flex items-center justify-between border-b border-gray-150 pb-3">
                    <div>
                        <h3 class="text-xs font-bold text-navy uppercase tracking-wider">Change Password</h3>
                        <p class="text-[11px] text-gray-400">Leave both fields blank if you do not want to alter the current password.</p>
                    </div>
                    <span class="text-[10px] font-bold uppercase text-gray-400 bg-gray-100 px-2.5 py-0.5 rounded">Optional</span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 pt-1">
                    <div>
                        <label for="password" class="block text-xs font-bold text-gray-700 mb-1.5">New Password</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                            </div>
                            <input type="password" name="password" id="password" placeholder="Leave empty to retain existing"
                                   class="w-full pl-10 text-sm border-gray-250 rounded-lg px-3.5 py-2.5 focus:border-cyan focus:ring-cyan transition">
                        </div>
                        @error('password')
                            <p class="text-red-500 text-xs mt-1 font-semibold">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="password_confirmation" class="block text-xs font-bold text-gray-700 mb-1.5">Confirm New Password</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                            </div>
                            <input type="password" name="password_confirmation" id="password_confirmation" placeholder="Confirm new password"
                                   class="w-full pl-10 text-sm border-gray-250 rounded-lg px-3.5 py-2.5 focus:border-cyan focus:ring-cyan transition">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Avatar & Email Verified Options -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 pt-2">
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Update Avatar Image</label>
                    <input type="file" name="avatar" id="avatar" accept="image/jpeg,image/png,image/jpg,image/webp"
                           class="block w-full text-xs text-gray-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-navy file:text-white hover:file:bg-opacity-90 file:cursor-pointer border border-gray-250 rounded-lg p-1.5 cursor-pointer">
                    <p class="text-[11px] text-gray-400 mt-1.5">Leave blank to keep existing avatar. PNG, JPG or WebP up to 2MB.</p>
                    @error('avatar')
                        <p class="text-red-500 text-xs mt-1 font-semibold">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-center pt-6">
                    <label class="relative flex items-start space-x-3 cursor-pointer select-none">
                        <input type="checkbox" name="email_verified" value="1" {{ old('email_verified', $user->email_verified_at ? '1' : '0') == '1' ? 'checked' : '' }}
                               class="w-4 h-4 text-cyan border-gray-300 rounded focus:ring-cyan mt-0.5">
                        <div>
                            <span class="text-sm font-bold text-navy">Email Address Verified</span>
                            <p class="text-xs text-gray-500">
                                @if($user->email_verified_at)
                                    Verified on {{ $user->email_verified_at->format('M d, Y H:i') }}
                                @else
                                    Not currently verified. Check this to grant verified status.
                                @endif
                            </p>
                        </div>
                    </label>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="border-t border-gray-200 pt-6 flex items-center justify-end space-x-4">
                <a href="{{ route('admin.users.index') }}" class="px-5 py-2.5 text-xs font-bold text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-lg transition">
                    Cancel
                </a>
                <button type="submit" class="inline-flex items-center space-x-2 px-6 py-2.5 bg-navy hover:bg-opacity-95 text-white text-xs font-bold rounded-lg shadow-sm hover:shadow transition">
                    <svg class="w-4 h-4 text-cyan" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    <span>Save User Changes</span>
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
