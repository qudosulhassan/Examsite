@extends('layouts.admin')

@section('content')
<div class="max-w-md mx-auto space-y-6">
    <div class="flex justify-between items-center">
        <h1 class="text-2xl font-bold text-gray-800">Edit User Role</h1>
        <a href="{{ route('admin.users.index') }}" class="text-sm text-navy hover:underline">
            ← Back to Listing
        </a>
    </div>

    <!-- Edit Form -->
    <div class="bg-white border border-gray-250 rounded-lg p-6 shadow-sm">
        <form action="{{ route('admin.users.update', $user->id) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')
            
            <!-- Email (Read Only) -->
            <div>
                <label class="block text-xs font-bold text-gray-400 uppercase mb-2">Email Address</label>
                <input type="email" value="{{ $user->email }}" readonly
                       class="w-full bg-gray-50 border-gray-300 rounded text-sm text-gray-500 px-3 py-2 cursor-not-allowed">
            </div>

            <!-- Name -->
            <div>
                <label for="name" class="block text-xs font-bold text-gray-400 uppercase mb-2">Full Name</label>
                <input type="text" name="name" id="name" required value="{{ old('name', $user->name) }}"
                       class="w-full border-gray-300 rounded text-sm px-3 py-2 focus:border-cyan focus:ring-cyan">
                @error('name')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Role Select -->
            <div>
                <label for="role" class="block text-xs font-bold text-gray-400 uppercase mb-2">System Role</label>
                <select name="role" id="role" required
                        class="w-full border-gray-300 rounded text-sm px-3 py-2 focus:border-cyan focus:ring-cyan">
                    <option value="student" {{ old('role', $user->role) === 'student' ? 'selected' : '' }}>Student (Default Portal Access)</option>
                    <option value="admin" {{ old('role', $user->role) === 'admin' ? 'selected' : '' }}>Administrator (Dashboard Access)</option>
                </select>
                @error('role')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Submit Buttons -->
            <div class="border-t border-gray-150 pt-6 flex justify-end space-x-3">
                <a href="{{ route('admin.users.index') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-600 text-xs font-bold py-2.5 px-6 rounded transition">
                    Cancel
                </a>
                <button type="submit" class="bg-navy hover:bg-opacity-95 text-white text-xs font-bold py-2.5 px-6 rounded shadow transition">
                    Update User Settings
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
