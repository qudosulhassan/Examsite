@extends('layouts.admin')

@section('content')
<div class="space-y-6" x-data="{
    selectedIds: [],
    bulkAction: '',
    selectAll: false,
    toggleAll() {
        if (this.selectAll) {
            this.selectedIds = Array.from(document.querySelectorAll('.post-checkbox')).map(el => parseInt(el.value));
        } else {
            this.selectedIds = [];
        }
    }
}">
    <!-- Header & Quick Actions -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-navy tracking-tight">Blog Posts</h1>
            <p class="text-xs text-gray-500 mt-1">Manage, filter, draft, schedule, and duplicate your articles.</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.blog.dashboard') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg text-xs font-bold text-gray-700 bg-white hover:bg-gray-50 shadow-sm transition">
                <svg class="w-4 h-4 mr-1.5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" /></svg>
                Blog Analytics
            </a>
            <a href="{{ route('admin.blog.create') }}" class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-navy to-slate-800 hover:from-slate-800 hover:to-navy text-white rounded-lg text-xs font-bold shadow-md hover:shadow transition transform hover:-translate-y-0.5">
                <svg class="w-4 h-4 mr-1.5 text-cyan" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" /></svg>
                + Add New Post
            </a>
        </div>
    </div>

    <!-- Status Tabs -->
    <div class="flex border-b border-gray-200 overflow-x-auto space-x-1 sm:space-x-2 text-xs font-bold">
        <a href="{{ route('admin.blog.index', array_merge(request()->except(['status', 'page']), ['status' => 'all'])) }}"
           class="px-4 py-3 border-b-2 flex items-center gap-2 whitespace-nowrap transition {{ $status === 'all' ? 'border-cyan text-navy' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
            <span>All Posts</span>
            <span class="px-2 py-0.5 rounded-full text-[10px] {{ $status === 'all' ? 'bg-navy text-white' : 'bg-gray-100 text-gray-600' }}">{{ $counts['all'] }}</span>
        </a>
        <a href="{{ route('admin.blog.index', array_merge(request()->except(['status', 'page']), ['status' => 'published'])) }}"
           class="px-4 py-3 border-b-2 flex items-center gap-2 whitespace-nowrap transition {{ $status === 'published' ? 'border-emerald-600 text-emerald-800' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
            <span>Published</span>
            <span class="px-2 py-0.5 rounded-full text-[10px] {{ $status === 'published' ? 'bg-emerald-600 text-white' : 'bg-gray-100 text-gray-600' }}">{{ $counts['published'] }}</span>
        </a>
        <a href="{{ route('admin.blog.index', array_merge(request()->except(['status', 'page']), ['status' => 'draft'])) }}"
           class="px-4 py-3 border-b-2 flex items-center gap-2 whitespace-nowrap transition {{ $status === 'draft' ? 'border-amber-500 text-amber-800' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
            <span>Drafts</span>
            <span class="px-2 py-0.5 rounded-full text-[10px] {{ $status === 'draft' ? 'bg-amber-500 text-white' : 'bg-gray-100 text-gray-600' }}">{{ $counts['draft'] }}</span>
        </a>
        <a href="{{ route('admin.blog.index', array_merge(request()->except(['status', 'page']), ['status' => 'scheduled'])) }}"
           class="px-4 py-3 border-b-2 flex items-center gap-2 whitespace-nowrap transition {{ $status === 'scheduled' ? 'border-blue-500 text-blue-800' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
            <span>Scheduled</span>
            <span class="px-2 py-0.5 rounded-full text-[10px] {{ $status === 'scheduled' ? 'bg-blue-500 text-white' : 'bg-gray-100 text-gray-600' }}">{{ $counts['scheduled'] }}</span>
        </a>
        <a href="{{ route('admin.blog.index', array_merge(request()->except(['status', 'page']), ['status' => 'trash'])) }}"
           class="px-4 py-3 border-b-2 flex items-center gap-2 whitespace-nowrap transition {{ $status === 'trash' ? 'border-rose-500 text-rose-800' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
            <span>Trash</span>
            <span class="px-2 py-0.5 rounded-full text-[10px] {{ $status === 'trash' ? 'bg-rose-500 text-white' : 'bg-gray-100 text-gray-600' }}">{{ $counts['trash'] }}</span>
        </a>
    </div>

    <!-- Filter & Search Toolbar -->
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4">
        <form method="GET" action="{{ route('admin.blog.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3 items-center">
            <input type="hidden" name="status" value="{{ $status }}">
            @if(request('filter'))
                <input type="hidden" name="filter" value="{{ request('filter') }}">
            @endif

            <!-- Search -->
            <div class="lg:col-span-5 relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                </div>
                <input type="text" name="search" value="{{ $search }}" placeholder="Search title, slug, content, author..."
                       class="pl-9 w-full text-xs border-gray-300 rounded-lg focus:border-cyan focus:ring-cyan py-2.5">
            </div>

            <!-- Category Filter -->
            <div class="lg:col-span-3">
                <select name="category_id" onchange="this.form.submit()" class="w-full text-xs border-gray-300 rounded-lg focus:border-cyan focus:ring-cyan py-2.5">
                    <option value="">All Categories</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ $categoryId == $category->id ? 'selected' : '' }}>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Sort By -->
            <div class="lg:col-span-3">
                <select name="sort" onchange="this.form.submit()" class="w-full text-xs border-gray-300 rounded-lg focus:border-cyan focus:ring-cyan py-2.5">
                    <option value="latest" {{ $sort === 'latest' ? 'selected' : '' }}>Sort: Newest First</option>
                    <option value="oldest" {{ $sort === 'oldest' ? 'selected' : '' }}>Sort: Oldest First</option>
                    <option value="views_desc" {{ $sort === 'views_desc' ? 'selected' : '' }}>Sort: Most Views</option>
                    <option value="updated" {{ $sort === 'updated' ? 'selected' : '' }}>Sort: Recently Updated</option>
                    <option value="title_asc" {{ $sort === 'title_asc' ? 'selected' : '' }}>Sort: Title (A-Z)</option>
                    <option value="title_desc" {{ $sort === 'title_desc' ? 'selected' : '' }}>Sort: Title (Z-A)</option>
                </select>
            </div>

            <!-- Action buttons -->
            <div class="lg:col-span-1 flex gap-2">
                <button type="submit" class="w-full bg-navy text-white text-xs font-bold py-2.5 px-3 rounded-lg hover:bg-slate-800 transition">
                    Filter
                </button>
                @if($search || $categoryId || $sort !== 'latest' || request('filter'))
                    <a href="{{ route('admin.blog.index', ['status' => $status]) }}" title="Clear Filters" class="inline-flex items-center justify-center p-2.5 border border-gray-300 rounded-lg text-gray-500 hover:text-navy hover:bg-gray-50">
                        ✕
                    </a>
                @endif
            </div>
        </form>

        <!-- Bulk Action Bar (Visible when items selected) -->
        <div x-show="selectedIds.length > 0" x-cloak class="mt-4 pt-4 border-t border-gray-200 flex flex-wrap items-center justify-between gap-3 bg-cyan/5 p-3 rounded-lg">
            <div class="flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-cyan animate-pulse"></span>
                <span class="text-xs font-bold text-navy" x-text="selectedIds.length + ' post(s) selected'"></span>
            </div>

            <form method="POST" action="{{ route('admin.blog.bulk-action') }}" class="flex items-center gap-2" onsubmit="return confirm('Execute this bulk action on selected posts?')">
                @csrf
                <template x-for="id in selectedIds" :key="id">
                    <input type="hidden" name="post_ids[]" :value="id">
                </template>

                <select name="action" required class="text-xs border-gray-300 rounded-lg focus:border-cyan focus:ring-cyan py-1.5 px-3 bg-white font-medium">
                    <option value="">Choose Bulk Action...</option>
                    @if($status !== 'trash')
                        <option value="publish">Publish Selected</option>
                        <option value="draft">Move to Drafts</option>
                        <option value="trash">Move to Trash</option>
                    @else
                        <option value="restore">Restore Selected</option>
                        <option value="force_delete">Permanently Delete</option>
                    @endif
                </select>

                <button type="submit" class="bg-navy hover:bg-slate-800 text-white text-xs font-bold py-1.5 px-4 rounded-lg transition shadow-sm">
                    Apply
                </button>
            </form>
        </div>
    </div>

    <!-- Active Health Filter Warning Banner -->
    @if(request('filter'))
        <div class="bg-amber-50 border-l-4 border-amber-500 p-4 rounded-r-lg flex items-center justify-between text-xs text-amber-800 font-semibold">
            <span>Filtered by Content Audit: <strong class="uppercase">{{ str_replace('_', ' ', request('filter')) }}</strong></span>
            <a href="{{ route('admin.blog.index', ['status' => $status]) }}" class="text-amber-900 underline font-bold">Clear Audit Filter</a>
        </div>
    @endif

    <!-- Posts Table -->
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-xs">
                <thead class="bg-gray-50/80">
                    <tr>
                        <th class="px-4 py-3.5 text-left w-10">
                            <input type="checkbox" x-model="selectAll" @change="toggleAll()" class="rounded border-gray-300 text-cyan focus:ring-cyan h-4 w-4">
                        </th>
                        <th class="px-4 py-3.5 text-left font-bold text-gray-400 uppercase tracking-wider">Article</th>
                        <th class="px-4 py-3.5 text-left font-bold text-gray-400 uppercase tracking-wider">Category</th>
                        <th class="px-4 py-3.5 text-left font-bold text-gray-400 uppercase tracking-wider">Author</th>
                        <th class="px-4 py-3.5 text-left font-bold text-gray-400 uppercase tracking-wider">Status</th>
                        <th class="px-4 py-3.5 text-left font-bold text-gray-400 uppercase tracking-wider">Engagement</th>
                        <th class="px-4 py-3.5 text-left font-bold text-gray-400 uppercase tracking-wider">Dates</th>
                        <th class="px-4 py-3.5 text-right font-bold text-gray-400 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-150 bg-white">
                    @forelse($posts as $post)
                        <tr class="hover:bg-gray-50/70 transition {{ $post->trashed() ? 'bg-rose-50/20' : '' }}">
                            <!-- Checkbox -->
                            <td class="px-4 py-4">
                                <input type="checkbox" :value="{{ $post->id }}" x-model="selectedIds" class="post-checkbox rounded border-gray-300 text-cyan focus:ring-cyan h-4 w-4">
                            </td>

                            <!-- Article Info -->
                            <td class="px-4 py-4">
                                <div class="flex items-start gap-3">
                                    @if($post->featured_image)
                                        <img src="{{ str_starts_with($post->featured_image, 'http') ? $post->featured_image : asset('storage/' . $post->featured_image) }}" alt="" class="w-12 h-12 rounded-lg object-cover flex-shrink-0 border border-gray-200">
                                    @else
                                        <div class="w-12 h-12 rounded-lg bg-gray-100 text-gray-400 font-bold flex items-center justify-center flex-shrink-0 border border-gray-200 text-xs">
                                            No Img
                                        </div>
                                    @endif
                                    <div class="min-w-0">
                                        <div class="flex items-center gap-2">
                                            <a href="{{ route('admin.blog.edit', $post->id) }}" class="font-bold text-navy hover:text-cyan text-sm block truncate max-w-md" title="{{ $post->title }}">
                                                {{ $post->title }}
                                            </a>
                                            @if($post->is_featured)
                                                <span class="inline-flex items-center px-1.5 py-0.2 rounded text-[9px] font-black uppercase tracking-wider bg-cyan/15 text-cyan">
                                                    ★ Featured
                                                </span>
                                            @endif
                                        </div>
                                        <div class="text-[11px] text-gray-400 mt-1 flex items-center gap-2">
                                            <span class="font-mono text-gray-500">/blog/{{ $post->slug }}</span>
                                            <span>•</span>
                                            <span>{{ $post->reading_time ?? 1 }} min read</span>
                                        </div>
                                    </div>
                                </div>
                            </td>

                            <!-- Category -->
                            <td class="px-4 py-4 whitespace-nowrap">
                                @if($post->category)
                                    <a href="{{ route('admin.blog.index', ['category_id' => $post->category->id]) }}" class="inline-flex items-center px-2.5 py-1 rounded-md text-[11px] font-bold bg-gray-100 text-gray-700 hover:bg-gray-200 transition">
                                        {{ $post->category->name }}
                                    </a>
                                @else
                                    <span class="text-rose-500 font-medium text-[11px]">Uncategorized</span>
                                @endif
                            </td>

                            <!-- Author -->
                            <td class="px-4 py-4 whitespace-nowrap">
                                <div class="flex items-center gap-2">
                                    <div class="w-6 h-6 rounded-full bg-navy/10 text-navy font-bold flex items-center justify-center text-[10px] uppercase">
                                        {{ substr($post->user->name ?? 'Admin', 0, 2) }}
                                    </div>
                                    <span class="font-medium text-gray-700 text-xs">{{ $post->user->name ?? 'Staff' }}</span>
                                </div>
                            </td>

                            <!-- Status -->
                            <td class="px-4 py-4 whitespace-nowrap">
                                @if($post->trashed())
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800">
                                        In Trash
                                    </span>
                                @elseif($post->status === 'published')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-1.5"></span>
                                        Published
                                    </span>
                                @elseif($post->status === 'draft')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500 mr-1.5"></span>
                                        Draft
                                    </span>
                                @elseif($post->status === 'scheduled')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-blue-100 text-blue-800">
                                        <span class="w-1.5 h-1.5 rounded-full bg-blue-500 mr-1.5"></span>
                                        Scheduled
                                    </span>
                                @endif
                            </td>

                            <!-- Engagement (Views & Comments) -->
                            <td class="px-4 py-4 whitespace-nowrap">
                                <div class="flex items-center gap-3">
                                    <span class="text-gray-700 font-semibold" title="Total Views">
                                        👁 {{ number_format($post->views_count) }}
                                    </span>
                                    <span class="text-gray-500" title="Total Comments">
                                        💬 {{ $post->comments ? $post->comments->count() : 0 }}
                                    </span>
                                </div>
                            </td>

                            <!-- Dates -->
                            <td class="px-4 py-4 whitespace-nowrap text-gray-500 text-[11px]">
                                <div><strong class="text-gray-700 font-medium">Pub:</strong> {{ $post->published_at ? $post->published_at->format('M d, Y') : 'Draft' }}</div>
                                <div class="text-[10px] text-gray-400 mt-0.5">Updated: {{ $post->updated_at->diffForHumans() }}</div>
                            </td>

                            <!-- Actions -->
                            <td class="px-4 py-4 whitespace-nowrap text-right font-medium">
                                <div class="flex items-center justify-end gap-2" x-data="{ menuOpen: false }">
                                    @if(!$post->trashed())
                                        <a href="{{ route('admin.blog.edit', $post->id) }}" class="p-1.5 text-cyan hover:text-cyan-dark hover:bg-cyan/10 rounded font-bold" title="Edit Post">
                                            Edit
                                        </a>

                                        <!-- Duplicate Form -->
                                        <form action="{{ route('admin.blog.duplicate', $post->id) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" class="p-1.5 text-gray-500 hover:text-navy hover:bg-gray-100 rounded font-semibold" title="Duplicate to new Draft">
                                                Copy
                                            </button>
                                        </form>

                                        @if($post->status === 'published')
                                            <a href="{{ route('blog.show', $post->slug) }}" target="_blank" class="p-1.5 text-gray-500 hover:text-navy hover:bg-gray-100 rounded" title="View Public Post">
                                                ↗
                                            </a>
                                        @endif

                                        <!-- Trash Action -->
                                        <form action="{{ route('admin.blog.destroy', $post->id) }}" method="POST" class="inline" onsubmit="return confirm('Move this post to trash?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-1.5 text-rose-500 hover:text-rose-700 hover:bg-rose-50 rounded font-semibold" title="Move to Trash">
                                                Trash
                                            </button>
                                        </form>
                                    @else
                                        <!-- Restore from Trash -->
                                        <form action="{{ route('admin.blog.restore', $post->id) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" class="text-emerald-600 hover:text-emerald-800 font-bold mr-2">
                                                Restore
                                            </button>
                                        </form>

                                        <!-- Permanent Delete -->
                                        <form action="{{ route('admin.blog.force-delete', $post->id) }}" method="POST" class="inline" onsubmit="return confirm('Permanently delete this post and its comments from the database? This cannot be undone.')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-rose-600 hover:text-rose-900 font-bold">
                                                Delete Forever
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-12 text-center text-gray-400">
                                <div class="max-w-xs mx-auto text-center space-y-2">
                                    <svg class="w-10 h-10 mx-auto text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z" /></svg>
                                    <p class="text-sm font-bold text-gray-600">No blog posts found</p>
                                    <p class="text-xs text-gray-400">Try changing your search keywords, status filter, or create a brand new article.</p>
                                    <div class="pt-2">
                                        <a href="{{ route('admin.blog.create') }}" class="inline-flex items-center px-4 py-2 bg-navy text-white text-xs font-bold rounded-lg hover:bg-slate-800">
                                            + Create Post
                                        </a>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination Bar -->
        @if($posts->hasPages())
            <div class="bg-gray-50/80 px-6 py-4 border-t border-gray-200">
                {{ $posts->links() }}
            </div>
        @endif
    </div>
</div>
@endsection