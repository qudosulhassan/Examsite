@extends('layouts.admin')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
        <h1 class="text-2xl font-bold text-gray-800">Manage Platform Users</h1>
    </div>

    <!-- Search Bar -->
    <div class="bg-white p-4 border border-gray-250 rounded-lg shadow-sm">
        <form action="{{ route('admin.users.index') }}" method="GET" class="flex gap-2">
            <input type="text" name="search" value="{{ $search }}" placeholder="Search by name or email address..."
                   class="flex-grow border-gray-300 rounded text-sm px-3 py-2 focus:border-cyan focus:ring-cyan">
            <button type="submit" class="bg-navy text-white text-xs font-bold px-6 py-2.5 rounded hover:bg-opacity-95 transition">
                Search
            </button>
            @if($search)
                <a href="{{ route('admin.users.index') }}" class="bg-gray-100 text-gray-600 text-xs font-bold px-4 py-2.5 rounded hover:bg-gray-200 transition flex items-center">
                    Clear
                </a>
            @endif
        </form>
    </div>

    <!-- Users Table -->
    <div class="bg-white rounded-lg border border-gray-250 shadow-sm overflow-hidden">
        <table class="min-w-full divide-y divide-gray-150">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">User Name</th>
                    <th class="px-6 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">Email Address</th>
                    <th class="px-6 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">System Role</th>
                    <th class="px-6 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">Joined Date</th>
                    <th class="px-6 py-3 text-right text-[10px] font-bold text-gray-400 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-150 text-xs">
                @forelse($users as $usr)
                    <tr>
                        <td class="px-6 py-4 font-bold text-navy flex items-center space-x-3">
                            <img class="h-7 w-7 rounded-full" src="{{ $usr->avatar ?? 'https://ui-avatars.com/api/?name='.urlencode($usr->name).'&color=0A1628&background=00D4AA' }}" alt="">
                            <span>{{ $usr->name }}</span>
                        </td>
                        <td class="px-6 py-4 text-gray-600 font-semibold">
                            {{ $usr->email }}
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-2.5 py-0.5 rounded-full text-[9px] font-bold uppercase {{ $usr->isAdmin() ? 'bg-orange bg-opacity-10 text-orange' : 'bg-cyan bg-opacity-10 text-navy' }}">
                                {{ $usr->role }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-gray-500 font-semibold">
                            {{ $usr->created_at->format('M d, Y') }}
                        </td>
                        <td class="px-6 py-4 text-right space-x-2 font-bold">
                            <a href="{{ route('admin.users.show', $usr->id) }}" class="text-navy hover:underline">View Access</a>
                            <a href="{{ route('admin.users.edit', $usr->id) }}" class="text-cyan hover:underline">Edit</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-8 text-center text-gray-400">
                            No platform users found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        
        <!-- Pagination -->
        @if($users->hasPages())
            <div class="bg-gray-50 px-6 py-4 border-t border-gray-150">
                {{ $users->appends(request()->query())->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
