@extends('layouts.admin')

@section('content')
<div class="space-y-6">
    <div class="flex justify-between items-center">
        <h1 class="text-2xl font-bold text-gray-800">Manage Blog Posts</h1>
        <a href="{{ route('admin.blog.create') }}" class="bg-navy hover:bg-opacity-95 text-white text-xs font-bold py-2 px-4 rounded shadow transition">
            + Create New Post
        </a>
    </div>

    <!-- Blog Posts Table -->
    <div class="bg-white rounded-lg border border-gray-250 shadow-sm overflow-hidden">
        <table class="min-w-full divide-y divide-gray-150">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">Title</th>
                    <th class="px-6 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">Published At</th>
                    <th class="px-6 py-3 text-right text-[10px] font-bold text-gray-400 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-150 text-xs">
                @forelse($posts as $post)
                    <tr>
                        <td class="px-6 py-4 font-bold text-navy">
                            <div class="text-gray-900">{{ $post->title }}</div>
                            <div class="text-[10px] text-gray-400 font-semibold">{{ $post->slug }}</div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-2.5 py-0.5 rounded-full text-[9px] font-bold uppercase {{ $post->status === 'published' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' }}">
                                {{ $post->status }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-gray-500 font-semibold">
                            {{ $post->published_at ? \Carbon\Carbon::parse($post->published_at)->format('M d, Y H:i') : 'N/A' }}
                        </td>
                        <td class="px-6 py-4 text-right space-x-2 font-bold">
                            <a href="{{ route('admin.blog.edit', $post->id) }}" class="text-cyan hover:underline">Edit</a>
                            <form action="{{ route('admin.blog.destroy', $post->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Are you sure you want to delete this blog post?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-500 hover:underline">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-6 py-8 text-center text-gray-400">
                            No blog posts found in database.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        
        <!-- Pagination -->
        @if($posts->hasPages())
            <div class="bg-gray-50 px-6 py-4 border-t border-gray-150">
                {{ $posts->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
