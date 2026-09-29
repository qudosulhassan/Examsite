@extends('layouts.admin')

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2 text-xs text-gray-400 font-semibold mb-1">
                <a href="{{ route('admin.dashboard') }}" class="hover:text-navy transition">Dashboard</a>
                <span>/</span>
                <span class="text-cyan font-bold">Reviews</span>
            </div>
            <h1 class="text-2xl font-extrabold text-navy tracking-tight">Verified Customer Reviews</h1>
            <p class="text-xs text-gray-500 mt-0.5">Create and moderate the testimonials shown on each exam page.</p>
        </div>

        <a href="{{ route('admin.reviews.create') }}" class="inline-flex items-center justify-center px-5 py-2.5 bg-orange hover:bg-opacity-90 text-white text-xs font-black rounded-xl shadow-md shadow-orange/20 transition-all transform hover:-translate-y-0.5">
            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Add Review
        </a>
    </div>

    <!-- Stat Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white rounded-2xl border border-gray-150 p-5 shadow-sm hover:shadow-md transition-all">
            <div class="flex items-center justify-between mb-3">
                <span class="text-[11px] font-bold text-gray-400 uppercase tracking-wider">Total</span>
                <div class="w-8 h-8 rounded-xl bg-navy/5 text-navy flex items-center justify-center font-bold text-sm">💬</div>
            </div>
            <div class="text-2xl font-black text-navy">{{ number_format($totalReviews) }}</div>
            <div class="text-[10px] text-gray-400 font-semibold mt-1">Reviews</div>
        </div>

        <div class="bg-white rounded-2xl border border-gray-150 p-5 shadow-sm hover:shadow-md transition-all">
            <div class="flex items-center justify-between mb-3">
                <span class="text-[11px] font-bold text-gray-400 uppercase tracking-wider">Approved</span>
                <div class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold text-sm">✓</div>
            </div>
            <div class="text-2xl font-black text-emerald-600">{{ number_format($approvedReviews) }}</div>
            <div class="text-[10px] text-gray-400 font-semibold mt-1">Live on exam pages</div>
        </div>

        <div class="bg-white rounded-2xl border border-gray-150 p-5 shadow-sm hover:shadow-md transition-all">
            <div class="flex items-center justify-between mb-3">
                <span class="text-[11px] font-bold text-gray-400 uppercase tracking-wider">Pending</span>
                <div class="w-8 h-8 rounded-xl bg-orange/10 text-orange flex items-center justify-center font-bold text-sm">⏳</div>
            </div>
            <div class="text-2xl font-black text-orange">{{ number_format($pendingReviews) }}</div>
            <div class="text-[10px] text-gray-400 font-semibold mt-1">Not yet approved</div>
        </div>

        <div class="bg-white rounded-2xl border border-gray-150 p-5 shadow-sm hover:shadow-md transition-all">
            <div class="flex items-center justify-between mb-3">
                <span class="text-[11px] font-bold text-gray-400 uppercase tracking-wider">Avg Rating</span>
                <div class="w-8 h-8 rounded-xl bg-cyan/10 text-cyan flex items-center justify-center font-bold text-sm">★</div>
            </div>
            <div class="text-2xl font-black text-navy">{{ $averageRating ?: '—' }}</div>
            <div class="text-[10px] text-gray-400 font-semibold mt-1">Across approved reviews</div>
        </div>
    </div>

    <!-- Search & Filter Bar -->
    <div class="bg-white border border-gray-200 rounded-2xl shadow-sm p-4 sm:p-5">
        <form action="{{ route('admin.reviews.index') }}" method="GET" class="flex flex-wrap items-end gap-3">
            <div class="flex-1" style="min-width: 240px;">
                <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1.5">Search</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </div>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search review text, reviewer, or exam..."
                           class="w-full h-[42px] pl-10 pr-3.5 bg-gray-50 border border-gray-200 rounded-xl text-xs text-navy placeholder-gray-400 focus:bg-white focus:border-cyan focus:ring-2 focus:ring-cyan/20 outline-none transition-all">
                </div>
            </div>

            <div style="min-width: 150px;">
                <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1.5">Status</label>
                <select name="status" class="w-full h-[42px] px-3.5 bg-gray-50 border border-gray-200 rounded-xl text-xs font-semibold text-navy focus:bg-white focus:border-cyan focus:ring-2 focus:ring-cyan/20 outline-none transition-all cursor-pointer">
                    <option value="">All Statuses</option>
                    <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approved</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                </select>
            </div>

            <div style="min-width: 130px;">
                <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1.5">Rating</label>
                <select name="rating" class="w-full h-[42px] px-3.5 bg-gray-50 border border-gray-200 rounded-xl text-xs font-semibold text-navy focus:bg-white focus:border-cyan focus:ring-2 focus:ring-cyan/20 outline-none transition-all cursor-pointer">
                    <option value="">Any Rating</option>
                    @for($i = 5; $i >= 1; $i--)
                        <option value="{{ $i }}" {{ (string) request('rating') === (string) $i ? 'selected' : '' }}>{{ $i }} Star{{ $i > 1 ? 's' : '' }}</option>
                    @endfor
                </select>
            </div>

            <div class="flex items-center gap-2">
                <button type="submit" class="h-[42px] px-5 bg-navy hover:bg-opacity-90 text-white text-xs font-bold rounded-xl shadow-sm transition inline-flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5 text-cyan" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    Search
                </button>
                @if(request()->anyFilled(['search', 'status', 'rating']))
                    <a href="{{ route('admin.reviews.index') }}" class="h-[42px] w-[42px] flex items-center justify-center bg-gray-100 hover:bg-red-50 hover:text-red-600 text-gray-500 rounded-xl border border-gray-200 transition" title="Reset filters">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Reviews Table -->
    <div class="bg-white rounded-lg border border-gray-250 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-150">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">Reviewer</th>
                        <th class="px-6 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">Exam</th>
                        <th class="px-6 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">Rating</th>
                        <th class="px-6 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">Review</th>
                        <th class="px-6 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-right text-[10px] font-bold text-gray-400 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-150 text-xs">
                    @forelse($reviews as $review)
                    <tr class="hover:bg-gray-50/80 transition">
                        <td class="px-6 py-4">
                            <div class="flex items-center space-x-3">
                                <div class="w-8 h-8 rounded-full bg-gradient-to-br from-cyan to-blue-500 text-white flex items-center justify-center shrink-0 font-bold text-[10px]">
                                    {{ strtoupper(substr($review->display_name, 0, 2)) }}
                                </div>
                                <div>
                                    <div class="font-bold text-navy">{{ $review->display_name }}</div>
                                    <div class="text-[10px] text-gray-400">{{ $review->user->email ?? 'Unknown account' }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-navy/5 text-navy border border-navy/10">
                                {{ $review->exam->exam_code ?? 'Deleted Exam' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-yellow-500 font-bold whitespace-nowrap">
                            {{ str_repeat('★', $review->rating) }}{{ str_repeat('☆', 5 - $review->rating) }}
                        </td>
                        <td class="px-6 py-4 text-gray-600 max-w-xs">
                            <div class="truncate">{{ $review->review_text }}</div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-2.5 py-0.5 rounded-full text-[9px] font-bold uppercase {{ $review->is_approved ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' }}">
                                {{ $review->is_approved ? 'Approved' : 'Pending' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right space-x-2 font-bold">
                            <a href="{{ route('admin.reviews.edit', $review->id) }}" class="text-cyan hover:underline">Edit</a>
                            <form action="{{ route('admin.reviews.destroy', $review->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Are you sure you want to delete this review?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-500 hover:underline">Delete</button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center text-gray-400">
                            <div class="flex flex-col items-center gap-2">
                                <svg class="w-8 h-8 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path></svg>
                                <span class="font-semibold">No reviews found.</span>
                                @if(request()->anyFilled(['search', 'status', 'rating']))
                                    <p class="text-[11px] text-gray-400">Try adjusting your search criteria or resetting filters.</p>
                                @else
                                    <p class="text-[11px] text-gray-400">Add your first verified customer review to get started.</p>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($reviews->hasPages())
        <div class="bg-gray-50 px-6 py-4 border-t border-gray-150">
            {{ $reviews->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
