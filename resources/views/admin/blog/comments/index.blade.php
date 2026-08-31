@extends('layouts.admin')

@section('content')
<div class="max-w-7xl mx-auto space-y-6">
    <div class="flex justify-between items-center">
        <h1 class="text-2xl font-bold text-gray-800">Blog Comments</h1>
        
        <div class="flex space-x-2">
            <a href="{{ route('admin.blog-comments.index', ['status' => 'all']) }}" class="px-4 py-2 text-sm font-medium rounded-md {{ request('status', 'all') === 'all' ? 'bg-navy text-white' : 'bg-white text-gray-700 border border-gray-300 hover:bg-gray-50' }}">All</a>
            <a href="{{ route('admin.blog-comments.index', ['status' => 'pending']) }}" class="px-4 py-2 text-sm font-medium rounded-md {{ request('status') === 'pending' ? 'bg-yellow-500 text-white' : 'bg-white text-gray-700 border border-gray-300 hover:bg-gray-50' }}">Pending</a>
            <a href="{{ route('admin.blog-comments.index', ['status' => 'approved']) }}" class="px-4 py-2 text-sm font-medium rounded-md {{ request('status') === 'approved' ? 'bg-green-600 text-white' : 'bg-white text-gray-700 border border-gray-300 hover:bg-gray-50' }}">Approved</a>
            <a href="{{ route('admin.blog-comments.index', ['status' => 'spam']) }}" class="px-4 py-2 text-sm font-medium rounded-md {{ request('status') === 'spam' ? 'bg-red-600 text-white' : 'bg-white text-gray-700 border border-gray-300 hover:bg-gray-50' }}">Spam</a>
        </div>
    </div>

    @if(session('success'))
        <div class="bg-green-50 border-l-4 border-green-500 p-4 mb-6">
            <p class="text-green-700 text-sm font-medium">{{ session('success') }}</p>
        </div>
    @endif

    <div class="bg-white shadow rounded-lg overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Author</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Comment</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Post</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Date</th>
                        <th class="px-6 py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($comments as $comment)
                        <tr class="{{ $comment->status === 'pending' ? 'bg-yellow-50' : '' }}">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">{{ $comment->author_name }}</div>
                                <div class="text-sm text-gray-500">{{ $comment->author_email }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm text-gray-900 max-w-xs truncate" title="{{ $comment->comment_text }}">{{ $comment->comment_text }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <a href="{{ route('blog.show', $comment->post->slug) }}" target="_blank" class="text-sm text-cyan hover:underline truncate inline-block max-w-[150px]" title="{{ $comment->post->title }}">
                                    {{ $comment->post->title }}
                                </a>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($comment->status === 'approved')
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Approved</span>
                                @elseif($comment->status === 'pending')
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">Pending</span>
                                @else
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Spam</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $comment->created_at->format('M d, Y H:i') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                                @if($comment->status !== 'approved')
                                    <form action="{{ route('admin.blog-comments.approve', $comment->id) }}" method="POST" class="inline">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="text-green-600 hover:text-green-900">Approve</button>
                                    </form>
                                @endif
                                
                                @if($comment->status !== 'spam')
                                    <form action="{{ route('admin.blog-comments.spam', $comment->id) }}" method="POST" class="inline">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="text-yellow-600 hover:text-yellow-900">Spam</button>
                                    </form>
                                @endif
                                
                                <form action="{{ route('admin.blog-comments.destroy', $comment->id) }}" method="POST" class="inline" onsubmit="return confirm('Delete this comment permanently?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-900">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-10 text-center text-gray-500">
                                No comments found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <div class="px-6 py-4 border-t border-gray-200">
            {{ $comments->links() }}
        </div>
    </div>
</div>
@endsection
