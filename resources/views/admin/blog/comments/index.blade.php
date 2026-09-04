@extends('layouts.admin')

@section('content')
<div class="space-y-6" x-data="{
    selectedIds: [],
    selectAll: false,
    replyModal: false,
    replyCommentId: null,
    replyAuthor: '',
    replySnippet: '',
    openReply(comment) {
        this.replyCommentId = comment.id;
        this.replyAuthor = comment.author_name;
        this.replySnippet = comment.comment_text;
        this.replyModal = true;
    },
    toggleAll() {
        if (this.selectAll) {
            this.selectedIds = Array.from(document.querySelectorAll('.comment-checkbox')).map(el => parseInt(el.value));
        } else {
            this.selectedIds = [];
        }
    }
}">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <a href="{{ route('admin.blog.index') }}" class="text-xs text-gray-500 hover:text-navy">&larr; Blog Posts</a>
            </div>
            <h1 class="text-2xl font-black text-navy tracking-tight mt-1">Comments Moderation Hub</h1>
            <p class="text-xs text-gray-500">Approve user thoughts, filter spam bots, trash malicious links, and reply as editorial admin.</p>
        </div>
    </div>

    <!-- Status Tabs -->
    <div class="flex border-b border-gray-200 overflow-x-auto space-x-1 sm:space-x-2 text-xs font-bold">
        <a href="{{ route('admin.blog-comments.index', ['status' => 'all']) }}"
           class="px-4 py-3 border-b-2 flex items-center gap-2 whitespace-nowrap transition {{ $status === 'all' ? 'border-cyan text-navy' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
            <span>All Comments</span>
            <span class="px-2 py-0.5 rounded-full text-[10px] {{ $status === 'all' ? 'bg-navy text-white' : 'bg-gray-100 text-gray-600' }}">{{ $counts['all'] }}</span>
        </a>
        <a href="{{ route('admin.blog-comments.index', ['status' => 'pending']) }}"
           class="px-4 py-3 border-b-2 flex items-center gap-2 whitespace-nowrap transition {{ $status === 'pending' ? 'border-amber-500 text-amber-800' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
            <span>Pending Moderation</span>
            <span class="px-2 py-0.5 rounded-full text-[10px] {{ $status === 'pending' ? 'bg-amber-500 text-white' : 'bg-gray-100 text-gray-600' }}">{{ $counts['pending'] }}</span>
        </a>
        <a href="{{ route('admin.blog-comments.index', ['status' => 'approved']) }}"
           class="px-4 py-3 border-b-2 flex items-center gap-2 whitespace-nowrap transition {{ $status === 'approved' ? 'border-emerald-600 text-emerald-800' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
            <span>Approved</span>
            <span class="px-2 py-0.5 rounded-full text-[10px] {{ $status === 'approved' ? 'bg-emerald-600 text-white' : 'bg-gray-100 text-gray-600' }}">{{ $counts['approved'] }}</span>
        </a>
        <a href="{{ route('admin.blog-comments.index', ['status' => 'spam']) }}"
           class="px-4 py-3 border-b-2 flex items-center gap-2 whitespace-nowrap transition {{ $status === 'spam' ? 'border-rose-500 text-rose-800' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
            <span>Spam</span>
            <span class="px-2 py-0.5 rounded-full text-[10px] {{ $status === 'spam' ? 'bg-rose-500 text-white' : 'bg-gray-100 text-gray-600' }}">{{ $counts['spam'] }}</span>
        </a>
        <a href="{{ route('admin.blog-comments.index', ['status' => 'trash']) }}"
           class="px-4 py-3 border-b-2 flex items-center gap-2 whitespace-nowrap transition {{ $status === 'trash' ? 'border-gray-600 text-gray-900' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
            <span>Trash</span>
            <span class="px-2 py-0.5 rounded-full text-[10px] {{ $status === 'trash' ? 'bg-gray-700 text-white' : 'bg-gray-100 text-gray-600' }}">{{ $counts['trash'] }}</span>
        </a>
    </div>

    <!-- Toolbar: Search & Bulk -->
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4">
        <form method="GET" action="{{ route('admin.blog-comments.index') }}" class="flex flex-col sm:flex-row items-center justify-between gap-3">
            <input type="hidden" name="status" value="{{ $status }}">
            <div class="w-full sm:w-96 relative">
                <input type="text" name="search" value="{{ $search }}" placeholder="Search author, email, comment text..."
                       class="w-full text-xs border-gray-300 rounded-lg pl-9 pr-4 py-2 focus:border-cyan focus:ring-cyan">
                <svg class="w-4 h-4 text-gray-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
            </div>
            <div class="flex items-center gap-2">
                <button type="submit" class="bg-navy text-white text-xs font-bold py-2 px-4 rounded-lg hover:bg-slate-800">
                    Search
                </button>
                @if($search)
                    <a href="{{ route('admin.blog-comments.index', ['status' => $status]) }}" class="text-xs text-gray-500 hover:text-navy underline">Clear</a>
                @endif
            </div>
        </form>

        <!-- Bulk Action Bar -->
        <div x-show="selectedIds.length > 0" x-cloak class="mt-4 pt-4 border-t border-gray-200 flex flex-wrap items-center justify-between gap-3 bg-amber-50/60 p-3 rounded-lg">
            <div class="flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-amber-500 animate-pulse"></span>
                <span class="text-xs font-bold text-navy" x-text="selectedIds.length + ' comment(s) selected'"></span>
            </div>

            <form method="POST" action="{{ route('admin.blog-comments.bulk-action') }}" class="flex items-center gap-2" onsubmit="return confirm('Apply bulk action to selected comments?')">
                @csrf
                <template x-for="id in selectedIds" :key="id">
                    <input type="hidden" name="comment_ids[]" :value="id">
                </template>

                <select name="action" required class="text-xs border-gray-300 rounded-lg focus:border-cyan focus:ring-cyan py-1.5 px-3 bg-white font-medium">
                    <option value="">Choose Bulk Action...</option>
                    @if($status !== 'trash')
                        <option value="approve">Approve</option>
                        <option value="pending">Mark Pending</option>
                        <option value="spam">Mark as Spam</option>
                        <option value="trash">Move to Trash</option>
                    @else
                        <option value="restore">Restore Comments</option>
                        <option value="force_delete">Permanently Delete</option>
                    @endif
                </select>

                <button type="submit" class="bg-navy hover:bg-slate-800 text-white text-xs font-bold py-1.5 px-4 rounded-lg transition shadow-sm">
                    Apply
                </button>
            </form>
        </div>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-xs">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3.5 text-left w-10">
                            <input type="checkbox" x-model="selectAll" @change="toggleAll()" class="rounded border-gray-300 text-cyan focus:ring-cyan h-4 w-4">
                        </th>
                        <th class="px-4 py-3.5 text-left font-bold text-gray-400 uppercase tracking-wider">Author</th>
                        <th class="px-4 py-3.5 text-left font-bold text-gray-400 uppercase tracking-wider">Comment Snippet</th>
                        <th class="px-4 py-3.5 text-left font-bold text-gray-400 uppercase tracking-wider">Target Post</th>
                        <th class="px-4 py-3.5 text-left font-bold text-gray-400 uppercase tracking-wider">Status</th>
                        <th class="px-4 py-3.5 text-left font-bold text-gray-400 uppercase tracking-wider">Submitted</th>
                        <th class="px-4 py-3.5 text-right font-bold text-gray-400 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-150 bg-white">
                    @forelse($comments as $comment)
                        <tr class="hover:bg-gray-50/70 transition {{ $comment->status === 'pending' ? 'bg-amber-50/30' : ($comment->trashed() ? 'bg-rose-50/20' : '') }}">
                            <td class="px-4 py-4">
                                <input type="checkbox" :value="{{ $comment->id }}" x-model="selectedIds" class="comment-checkbox rounded border-gray-300 text-cyan focus:ring-cyan h-4 w-4">
                            </td>
                            <td class="px-4 py-4 whitespace-nowrap">
                                <div class="font-bold text-navy">{{ $comment->author_name }}</div>
                                <div class="text-[11px] text-gray-400 font-mono">{{ $comment->author_email }}</div>
                            </td>
                            <td class="px-4 py-4">
                                <div class="text-gray-800 text-xs max-w-sm line-clamp-2" title="{{ $comment->comment_text }}">
                                    {{ $comment->comment_text }}
                                </div>
                                @if($comment->parent_id)
                                    <span class="inline-block mt-1 text-[10px] font-semibold text-gray-400 bg-gray-100 px-1.5 py-0.5 rounded">
                                        ↳ Reply to #{{ $comment->parent_id }}
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-4 whitespace-nowrap">
                                @if($comment->post)
                                    <a href="{{ route('blog.show', $comment->post->slug) }}" target="_blank" class="text-cyan font-bold hover:underline truncate block max-w-xs" title="{{ $comment->post->title }}">
                                        {{ $comment->post->title }}
                                    </a>
                                @else
                                    <span class="text-gray-400 italic">Deleted Post</span>
                                @endif
                            </td>
                            <td class="px-4 py-4 whitespace-nowrap">
                                @if($comment->trashed())
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800">In Trash</span>
                                @elseif($comment->status === 'approved')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">Approved</span>
                                @elseif($comment->status === 'pending')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 animate-pulse">Pending</span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800">Spam</span>
                                @endif
                            </td>
                            <td class="px-4 py-4 whitespace-nowrap text-gray-500 text-[11px]">
                                {{ $comment->created_at->format('M d, Y H:i') }}
                            </td>
                            <td class="px-4 py-4 whitespace-nowrap text-right space-x-2 font-medium">
                                @if(!$comment->trashed())
                                    @if($comment->status !== 'approved')
                                        <form action="{{ route('admin.blog-comments.approve', $comment->id) }}" method="POST" class="inline">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="text-emerald-600 hover:text-emerald-900 font-bold" title="Approve">
                                                Approve
                                            </button>
                                        </form>
                                    @endif

                                    <button type="button" @click="openReply({{ json_encode($comment) }})" class="text-cyan hover:underline font-bold" title="Reply to comment">
                                        Reply
                                    </button>

                                    @if($comment->status !== 'spam')
                                        <form action="{{ route('admin.blog-comments.spam', $comment->id) }}" method="POST" class="inline">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="text-amber-600 hover:text-amber-900 font-bold" title="Mark Spam">
                                                Spam
                                            </button>
                                        </form>
                                    @endif

                                    <form action="{{ route('admin.blog-comments.destroy', $comment->id) }}" method="POST" class="inline" onsubmit="return confirm('Move comment to trash?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-rose-500 hover:text-rose-800 font-bold" title="Move to Trash">
                                            Trash
                                        </button>
                                    </form>
                                @else
                                    <form action="{{ route('admin.blog-comments.restore', $comment->id) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="text-emerald-600 hover:text-emerald-800 font-bold mr-2">
                                            Restore
                                        </button>
                                    </form>

                                    <form action="{{ route('admin.blog-comments.force-delete', $comment->id) }}" method="POST" class="inline" onsubmit="return confirm('Permanently delete comment?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-rose-600 hover:text-rose-900 font-bold">
                                            Delete Forever
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-gray-400">
                                No comments found in this view.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($comments->hasPages())
            <div class="px-6 py-4 border-t border-gray-200 bg-gray-50/50">
                {{ $comments->links() }}
            </div>
        @endif
    </div>

    <!-- Admin Reply Modal -->
    <div x-show="replyModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4">
        <div class="bg-white rounded-xl shadow-2xl max-w-lg w-full p-6 space-y-4" @click.away="replyModal = false">
            <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                <h3 class="text-base font-bold text-navy">Reply to Comment</h3>
                <button type="button" @click="replyModal = false" class="text-gray-400 hover:text-gray-600 text-lg">&times;</button>
            </div>

            <!-- Context Quote -->
            <div class="p-3 bg-gray-50 border-l-4 border-cyan rounded text-xs text-gray-600">
                <strong class="block text-navy font-bold mb-1" x-text="'Author: ' + replyAuthor"></strong>
                <p class="italic line-clamp-3" x-text="replySnippet"></p>
            </div>

            <form :action="'{{ url('admin/blog-comments') }}/' + replyCommentId + '/reply'" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Your Admin Response *</label>
                    <textarea name="reply_text" rows="4" required class="w-full text-xs border-gray-300 rounded-lg focus:border-cyan focus:ring-cyan py-2" placeholder="Write an editorial response to this user..."></textarea>
                </div>
                <div class="flex justify-end gap-2 pt-2 border-t border-gray-100">
                    <button type="button" @click="replyModal = false" class="px-4 py-2 text-xs font-bold text-gray-600 hover:bg-gray-100 rounded-lg">Cancel</button>
                    <button type="submit" class="px-4 py-2 text-xs font-bold text-white bg-navy hover:bg-slate-800 rounded-lg shadow">Post Approved Reply</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection