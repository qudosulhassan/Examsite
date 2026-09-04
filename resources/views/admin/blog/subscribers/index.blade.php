@extends('layouts.admin')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <a href="{{ route('admin.blog.index') }}" class="text-xs text-gray-500 hover:text-navy">&larr; Blog Posts</a>
            </div>
            <h1 class="text-2xl font-black text-navy tracking-tight mt-1">Newsletter Subscribers</h1>
            <p class="text-xs text-gray-500">Track email signups, monitor subscription retention, and export audience lists.</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.blog-subscribers.export', ['status' => $status]) }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-lg text-xs font-bold text-gray-700 hover:bg-gray-50 shadow-sm transition">
                <svg class="w-4 h-4 mr-1.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg>
                Export CSV List
            </a>
        </div>
    </div>

    <!-- Status Tabs -->
    <div class="flex border-b border-gray-200 overflow-x-auto space-x-1 sm:space-x-2 text-xs font-bold">
        <a href="{{ route('admin.blog-subscribers.index', ['status' => 'all']) }}"
           class="px-4 py-3 border-b-2 flex items-center gap-2 whitespace-nowrap transition {{ $status === 'all' ? 'border-cyan text-navy' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
            <span>All Subscribers</span>
            <span class="px-2 py-0.5 rounded-full text-[10px] {{ $status === 'all' ? 'bg-navy text-white' : 'bg-gray-100 text-gray-600' }}">{{ $counts['all'] }}</span>
        </a>
        <a href="{{ route('admin.blog-subscribers.index', ['status' => 'active']) }}"
           class="px-4 py-3 border-b-2 flex items-center gap-2 whitespace-nowrap transition {{ $status === 'active' ? 'border-emerald-600 text-emerald-800' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
            <span>Active Audience</span>
            <span class="px-2 py-0.5 rounded-full text-[10px] {{ $status === 'active' ? 'bg-emerald-600 text-white' : 'bg-gray-100 text-gray-600' }}">{{ $counts['active'] }}</span>
        </a>
        <a href="{{ route('admin.blog-subscribers.index', ['status' => 'unsubscribed']) }}"
           class="px-4 py-3 border-b-2 flex items-center gap-2 whitespace-nowrap transition {{ $status === 'unsubscribed' ? 'border-rose-500 text-rose-800' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
            <span>Unsubscribed</span>
            <span class="px-2 py-0.5 rounded-full text-[10px] {{ $status === 'unsubscribed' ? 'bg-rose-500 text-white' : 'bg-gray-100 text-gray-600' }}">{{ $counts['unsubscribed'] }}</span>
        </a>
    </div>

    <!-- Search Toolbar -->
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4">
        <form method="GET" action="{{ route('admin.blog-subscribers.index') }}" class="flex flex-col sm:flex-row items-center justify-between gap-3">
            <input type="hidden" name="status" value="{{ $status }}">
            <div class="w-full sm:w-96 relative">
                <input type="text" name="search" value="{{ $search }}" placeholder="Search email, name, source..."
                       class="w-full text-xs border-gray-300 rounded-lg pl-9 pr-4 py-2 focus:border-cyan focus:ring-cyan">
                <svg class="w-4 h-4 text-gray-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
            </div>
            <div class="flex items-center gap-2">
                <button type="submit" class="bg-navy text-white text-xs font-bold py-2 px-4 rounded-lg hover:bg-slate-800">
                    Search
                </button>
                @if($search)
                    <a href="{{ route('admin.blog-subscribers.index', ['status' => $status]) }}" class="text-xs text-gray-500 hover:text-navy underline">Clear</a>
                @endif
            </div>
        </form>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-xs">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3.5 text-left font-bold text-gray-400 uppercase tracking-wider">Email & Name</th>
                        <th class="px-6 py-3.5 text-left font-bold text-gray-400 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3.5 text-left font-bold text-gray-400 uppercase tracking-wider">Subscribed Date</th>
                        <th class="px-6 py-3.5 text-left font-bold text-gray-400 uppercase tracking-wider">Source / Campaign</th>
                        <th class="px-6 py-3.5 text-left font-bold text-gray-400 uppercase tracking-wider">IP Address</th>
                        <th class="px-6 py-3.5 text-right font-bold text-gray-400 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-150 bg-white">
                    @forelse($subscribers as $subscriber)
                        <tr class="hover:bg-gray-50/70 transition">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="font-bold text-navy">{{ $subscriber->email }}</div>
                                <div class="text-[11px] text-gray-400">{{ $subscriber->first_name ?: 'Anonymous' }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($subscriber->status === 'active')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                        Active
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800">
                                        Unsubscribed
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-gray-600 text-[11px]">
                                {{ $subscriber->subscribed_at ? $subscriber->subscribed_at->format('M d, Y H:i') : $subscriber->created_at->format('M d, Y') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-0.5 rounded bg-gray-100 text-gray-700 text-[11px] font-mono">
                                    {{ $subscriber->source ?: 'website_footer' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap font-mono text-gray-400 text-[11px]">
                                {{ $subscriber->ip_address ?: '—' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right space-x-2 font-medium">
                                <form action="{{ route('admin.blog-subscribers.toggle', $subscriber->id) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="text-cyan font-bold hover:underline">
                                        {{ $subscriber->status === 'active' ? 'Unsubscribe' : 'Reactivate' }}
                                    </button>
                                </form>

                                <form action="{{ route('admin.blog-subscribers.destroy', $subscriber->id) }}" method="POST" class="inline" onsubmit="return confirm('Remove subscriber record?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-rose-500 font-bold hover:underline">
                                        Delete
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-gray-400">
                                No newsletter subscribers found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($subscribers->hasPages())
            <div class="px-6 py-4 border-t border-gray-200 bg-gray-50/50">
                {{ $subscribers->links() }}
            </div>
        @endif
    </div>
</div>
@endsection