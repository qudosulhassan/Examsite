@extends('layouts.admin')

@section('content')
<div class="space-y-8">
    <!-- Top Header & Action Row -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-2 border-b border-gray-200">
        <div>
            <h1 class="text-2xl font-black text-navy tracking-tight">Blog & Content Management</h1>
            <p class="text-xs text-gray-500 mt-1">Real-time content performance, editorial health audits, and audience growth analytics.</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.blog.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg text-xs font-bold text-gray-700 bg-white hover:bg-gray-50 shadow-sm transition">
                <svg class="w-4 h-4 mr-1.5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16" /></svg>
                Manage Posts
            </a>
            <a href="{{ route('admin.blog.create') }}" class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-navy to-slate-800 hover:from-slate-800 hover:to-navy text-white rounded-lg text-xs font-bold shadow-md hover:shadow transition transform hover:-translate-y-0.5">
                <svg class="w-4 h-4 mr-1.5 text-cyan" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" /></svg>
                + New Blog Post
            </a>
        </div>
    </div>

    <!-- Primary KPI Metric Cards (Real Database Data) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        <!-- Total Posts -->
        <div class="bg-white rounded-xl p-5 border border-gray-200/80 shadow-sm hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-gray-400">Total Posts</span>
                <div class="w-9 h-9 rounded-lg bg-navy/5 text-navy flex items-center justify-center font-bold">
                    <svg class="w-5 h-5 text-navy" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z" /></svg>
                </div>
            </div>
            <div class="mt-3 flex items-baseline justify-between">
                <span class="text-2xl font-black text-navy">{{ number_format($totalPosts) }}</span>
                <span class="text-[11px] font-semibold text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-full">+{{ $postsThisMonth }} this mo.</span>
            </div>
            <div class="mt-3 flex items-center gap-3 text-[11px] text-gray-500 pt-3 border-t border-gray-100">
                <span class="font-medium text-emerald-600">{{ $publishedPosts }} Published</span>
                <span>•</span>
                <span class="font-medium text-amber-600">{{ $draftPosts }} Drafts</span>
                <span>•</span>
                <span class="font-medium text-blue-600">{{ $scheduledPosts }} Sched</span>
            </div>
        </div>

        <!-- Total Traffic / Views -->
        <div class="bg-white rounded-xl p-5 border border-gray-200/80 shadow-sm hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-gray-400">Content Views</span>
                <div class="w-9 h-9 rounded-lg bg-cyan/10 text-cyan flex items-center justify-center font-bold">
                    <svg class="w-5 h-5 text-cyan" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                </div>
            </div>
            <div class="mt-3 flex items-baseline justify-between">
                <span class="text-2xl font-black text-navy">{{ number_format($overallViews) }}</span>
                <span class="text-[11px] font-semibold text-cyan-700 bg-cyan/10 px-2 py-0.5 rounded-full">{{ number_format($uniqueVisitors) }} Visitors</span>
            </div>
            <div class="mt-3 flex items-center gap-2 text-[11px] text-gray-500 pt-3 border-t border-gray-100">
                <span class="text-gray-600 font-semibold">{{ number_format($viewsThisMonth) }} Views this month</span>
            </div>
        </div>

        <!-- Comments & Engagement -->
        <div class="bg-white rounded-xl p-5 border border-gray-200/80 shadow-sm hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-gray-400">User Comments</span>
                <div class="w-9 h-9 rounded-lg bg-amber-500/10 text-amber-600 flex items-center justify-center font-bold">
                    <svg class="w-5 h-5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" /></svg>
                </div>
            </div>
            <div class="mt-3 flex items-baseline justify-between">
                <span class="text-2xl font-black text-navy">{{ number_format($totalComments) }}</span>
                @if($pendingComments > 0)
                    <a href="{{ route('admin.blog-comments.index', ['status' => 'pending']) }}" class="text-[11px] font-bold text-amber-700 bg-amber-100 px-2 py-0.5 rounded-full hover:bg-amber-200 transition">
                        {{ $pendingComments }} Pending Review
                    </a>
                @else
                    <span class="text-[11px] font-medium text-gray-400 bg-gray-100 px-2 py-0.5 rounded-full">All Moderated</span>
                @endif
            </div>
            <div class="mt-3 flex items-center gap-3 text-[11px] text-gray-500 pt-3 border-t border-gray-100">
                <span class="font-medium text-emerald-600">{{ $approvedComments }} Approved</span>
                <span>•</span>
                <span class="font-medium text-rose-500">{{ $spamComments }} Spam</span>
            </div>
        </div>

        <!-- Newsletter Subscribers -->
        <div class="bg-white rounded-xl p-5 border border-gray-200/80 shadow-sm hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-gray-400">Subscribers</span>
                <div class="w-9 h-9 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center font-bold">
                    <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
                </div>
            </div>
            <div class="mt-3 flex items-baseline justify-between">
                <span class="text-2xl font-black text-navy">{{ number_format($totalSubscribers) }}</span>
                <span class="text-[11px] font-semibold text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-full">+{{ $subscribersThisMonth }} this mo.</span>
            </div>
            <div class="mt-3 flex items-center justify-between text-[11px] text-gray-500 pt-3 border-t border-gray-100">
                <span class="font-medium text-emerald-600">{{ $activeSubscribers }} Active Audience</span>
                <a href="{{ route('admin.blog-subscribers.index') }}" class="text-cyan font-bold hover:underline">Manage &rarr;</a>
            </div>
        </div>
    </div>

    <!-- Content Health Audit Card -->
    <div class="bg-white rounded-xl border border-gray-200/80 shadow-sm p-6">
        <div class="flex items-center justify-between mb-4">
            <div class="flex items-center gap-2">
                <span class="flex h-3 w-3 relative">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-cyan opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-3 w-3 bg-cyan"></span>
                </span>
                <h2 class="text-sm font-black text-navy uppercase tracking-wider">Content Health & SEO Audit</h2>
            </div>
            <span class="text-xs text-gray-400 font-semibold">{{ count($healthIssues) === 0 ? 'All Checks Passing' : count($healthIssues) . ' Attention Items' }}</span>
        </div>

        @if(count($healthIssues) === 0)
            <div class="bg-emerald-50 border border-emerald-200 rounded-lg p-4 flex items-center gap-3">
                <div class="w-8 h-8 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center font-bold">✓</div>
                <div>
                    <h3 class="text-xs font-bold text-emerald-900">Excellent Content Health!</h3>
                    <p class="text-[11px] text-emerald-700">All your published and draft posts have meta descriptions, featured thumbnail images, and assigned categories.</p>
                </div>
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                @foreach($healthIssues as $issue)
                    <div class="p-4 rounded-lg border {{ $issue['type'] === 'danger' ? 'bg-rose-50/50 border-rose-200' : ($issue['type'] === 'warning' ? 'bg-amber-50/50 border-amber-200' : 'bg-blue-50/50 border-blue-200') }} flex flex-col justify-between">
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full {{ $issue['type'] === 'danger' ? 'bg-rose-500' : ($issue['type'] === 'warning' ? 'bg-amber-500' : 'bg-blue-500') }}"></span>
                                <h4 class="text-xs font-bold text-gray-900">{{ $issue['title'] }}</h4>
                            </div>
                            <p class="text-[11px] text-gray-600 mt-2 leading-relaxed">{{ $issue['description'] }}</p>
                        </div>
                        <div class="mt-4 pt-3 border-t border-gray-200/50">
                            <a href="{{ $issue['action_url'] }}" class="text-xs font-bold {{ $issue['type'] === 'danger' ? 'text-rose-600 hover:text-rose-800' : ($issue['type'] === 'warning' ? 'text-amber-700 hover:text-amber-900' : 'text-blue-600 hover:text-blue-800') }} flex items-center justify-between">
                                <span>{{ $issue['action_label'] }}</span>
                                <span>&rarr;</span>
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <!-- Two Column Grid: Top Performing Posts & Quick Taxonomy Shortcuts -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Recent Posts Table (2 cols) -->
        <div class="lg:col-span-2 bg-white rounded-xl border border-gray-200/80 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-150 flex items-center justify-between">
                <div>
                    <h2 class="text-sm font-bold text-navy uppercase tracking-wider">Recent Blog Posts</h2>
                    <p class="text-xs text-gray-400">Latest articles created or updated in the CMS.</p>
                </div>
                <a href="{{ route('admin.blog.index') }}" class="text-xs font-bold text-cyan hover:underline">
                    View All Posts &rarr;
                </a>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-150 text-xs">
                    <thead class="bg-gray-50/80">
                        <tr>
                            <th class="px-5 py-3 text-left font-bold text-gray-400 uppercase tracking-wider">Post Details</th>
                            <th class="px-4 py-3 text-left font-bold text-gray-400 uppercase tracking-wider">Status</th>
                            <th class="px-4 py-3 text-left font-bold text-gray-400 uppercase tracking-wider">Views</th>
                            <th class="px-4 py-3 text-right font-bold text-gray-400 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-150 bg-white">
                        @forelse($recentPosts as $post)
                            <tr class="hover:bg-gray-50/70 transition">
                                <td class="px-5 py-3.5">
                                    <div class="flex items-center gap-3">
                                        @if($post->featured_image)
                                            <img src="{{ str_starts_with($post->featured_image, 'http') ? $post->featured_image : asset('storage/' . $post->featured_image) }}" alt="" class="w-10 h-10 rounded-md object-cover flex-shrink-0 border border-gray-200">
                                        @else
                                            <div class="w-10 h-10 rounded-md bg-navy/5 text-navy font-bold flex items-center justify-center flex-shrink-0 border border-gray-200 text-xs">
                                                IMG
                                            </div>
                                        @endif
                                        <div class="min-w-0">
                                            <a href="{{ route('admin.blog.edit', $post->id) }}" class="font-bold text-gray-900 hover:text-cyan truncate block max-w-sm">
                                                {{ $post->title }}
                                            </a>
                                            <div class="text-[11px] text-gray-400 mt-0.5 flex items-center gap-2">
                                                <span>{{ $post->category ? $post->category->name : 'Uncategorized' }}</span>
                                                <span>•</span>
                                                <span>{{ $post->user ? $post->user->name : 'Staff' }}</span>
                                                <span>•</span>
                                                <span>{{ $post->created_at->format('M d, Y') }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3.5 whitespace-nowrap">
                                    @if($post->status === 'published')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                            Published
                                        </span>
                                    @elseif($post->status === 'draft')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800">
                                            Draft
                                        </span>
                                    @elseif($post->status === 'scheduled')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-100 text-blue-800">
                                            Scheduled
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-3.5 whitespace-nowrap font-bold text-gray-700">
                                    {{ number_format($post->views_count) }}
                                </td>
                                <td class="px-4 py-3.5 whitespace-nowrap text-right space-x-2">
                                    <a href="{{ route('admin.blog.edit', $post->id) }}" class="text-cyan font-bold hover:underline">Edit</a>
                                    @if($post->status === 'published')
                                        <a href="{{ route('blog.show', $post->slug) }}" target="_blank" class="text-gray-500 font-medium hover:text-navy">View</a>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-8 text-center text-gray-400">
                                    No posts created yet.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Sidebar Column: Taxonomy Stats & Quick Shortcuts -->
        <div class="space-y-6">
            <!-- Taxonomy Overview -->
            <div class="bg-white rounded-xl border border-gray-200/80 shadow-sm p-6">
                <h3 class="text-xs font-bold text-navy uppercase tracking-wider mb-4">Taxonomy & Organization</h3>
                
                <div class="space-y-3">
                    <div class="flex items-center justify-between p-3 rounded-lg bg-gray-50 border border-gray-150">
                        <div class="flex items-center gap-2.5">
                            <div class="w-7 h-7 rounded-md bg-cyan/10 text-cyan flex items-center justify-center font-bold text-xs">C</div>
                            <div>
                                <h4 class="text-xs font-bold text-navy">Categories</h4>
                                <span class="text-[11px] text-gray-500">{{ $categoriesCount }} defined</span>
                            </div>
                        </div>
                        <a href="{{ route('admin.blog-categories.index') }}" class="text-xs font-bold text-cyan hover:underline">Manage &rarr;</a>
                    </div>

                    <div class="flex items-center justify-between p-3 rounded-lg bg-gray-50 border border-gray-150">
                        <div class="flex items-center gap-2.5">
                            <div class="w-7 h-7 rounded-md bg-navy/10 text-navy flex items-center justify-center font-bold text-xs">#</div>
                            <div>
                                <h4 class="text-xs font-bold text-navy">Blog Tags</h4>
                                <span class="text-[11px] text-gray-500">{{ $tagsCount }} tags</span>
                            </div>
                        </div>
                        <a href="{{ route('admin.blog-tags.index') }}" class="text-xs font-bold text-cyan hover:underline">Manage &rarr;</a>
                    </div>

                    <div class="flex items-center justify-between p-3 rounded-lg bg-gray-50 border border-gray-150">
                        <div class="flex items-center gap-2.5">
                            <div class="w-7 h-7 rounded-md bg-amber-100 text-amber-700 flex items-center justify-center font-bold text-xs">💬</div>
                            <div>
                                <h4 class="text-xs font-bold text-navy">Comments Queue</h4>
                                <span class="text-[11px] text-gray-500">{{ $pendingComments }} pending</span>
                            </div>
                        </div>
                        <a href="{{ route('admin.blog-comments.index') }}" class="text-xs font-bold text-cyan hover:underline">Moderate &rarr;</a>
                    </div>
                </div>
            </div>

            <!-- Top Performing Posts Card -->
            <div class="bg-white rounded-xl border border-gray-200/80 shadow-sm p-6">
                <h3 class="text-xs font-bold text-navy uppercase tracking-wider mb-4">Top 5 Most Viewed Articles</h3>
                <div class="space-y-3">
                    @forelse($topPosts as $idx => $tPost)
                        <div class="flex items-start gap-3">
                            <span class="w-5 h-5 rounded-full bg-navy/5 text-navy font-black flex items-center justify-center text-[10px] flex-shrink-0 mt-0.5">
                                {{ $idx + 1 }}
                            </span>
                            <div class="min-w-0 flex-1">
                                <a href="{{ route('admin.blog.edit', $tPost->id) }}" class="text-xs font-bold text-gray-900 hover:text-cyan truncate block">
                                    {{ $tPost->title }}
                                </a>
                                <div class="text-[11px] text-gray-400 mt-0.5 flex items-center justify-between">
                                    <span>{{ $tPost->category ? $tPost->category->name : 'Uncategorized' }}</span>
                                    <span class="font-bold text-navy">{{ number_format($tPost->views_count) }} views</span>
                                </div>
                            </div>
                        </div>
                    @empty
                        <p class="text-xs text-gray-400 text-center py-4">No published articles yet.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection