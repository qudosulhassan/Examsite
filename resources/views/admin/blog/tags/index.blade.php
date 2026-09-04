@extends('layouts.admin')

@section('content')
<div class="space-y-6" x-data="{
    createModal: false,
    editModal: false,
    editId: null,
    editName: '',
    editSlug: '',
    openEdit(tag) {
        this.editId = tag.id;
        this.editName = tag.name;
        this.editSlug = tag.slug;
        this.editModal = true;
    }
}">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <a href="{{ route('admin.blog.index') }}" class="text-xs text-gray-500 hover:text-navy">&larr; Blog Posts</a>
            </div>
            <h1 class="text-2xl font-black text-navy tracking-tight mt-1">Blog Tags</h1>
            <p class="text-xs text-gray-500">Fine-grained keywords and cross-cutting topics attached to articles.</p>
        </div>
        <button type="button" @click="createModal = true" class="inline-flex items-center px-4 py-2 bg-navy hover:bg-slate-800 text-white rounded-lg text-xs font-bold shadow transition">
            + Add New Tag
        </button>
    </div>

    <!-- Search & List -->
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="p-4 border-b border-gray-200 bg-gray-50/50 flex flex-col sm:flex-row justify-between items-center gap-3">
            <form method="GET" action="{{ route('admin.blog-tags.index') }}" class="w-full sm:w-80 relative">
                <input type="text" name="search" value="{{ $search }}" placeholder="Search tags..."
                       class="w-full text-xs border-gray-300 rounded-lg pl-8 pr-4 py-2 focus:border-cyan focus:ring-cyan">
                <svg class="w-4 h-4 text-gray-400 absolute left-2.5 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
            </form>
            <span class="text-xs text-gray-500 font-semibold">{{ $tags->total() }} Tags Total</span>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-xs">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left font-bold text-gray-400 uppercase tracking-wider">Tag Name</th>
                        <th class="px-6 py-3 text-left font-bold text-gray-400 uppercase tracking-wider">Slug</th>
                        <th class="px-6 py-3 text-center font-bold text-gray-400 uppercase tracking-wider">Usage Count</th>
                        <th class="px-6 py-3 text-right font-bold text-gray-400 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-150 bg-white">
                    @forelse($tags as $tag)
                        <tr class="hover:bg-gray-50/70 transition">
                            <td class="px-6 py-4 font-bold text-navy whitespace-nowrap">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md bg-gray-100 text-navy font-bold text-xs">
                                    #{{ $tag->name }}
                                </span>
                            </td>
                            <td class="px-6 py-4 font-mono text-gray-500 whitespace-nowrap">
                                /blog/tag/{{ $tag->slug }}
                            </td>
                            <td class="px-6 py-4 text-center whitespace-nowrap">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold {{ $tag->posts_count > 0 ? 'bg-cyan/15 text-cyan' : 'bg-gray-100 text-gray-400' }}">
                                    {{ $tag->posts_count }} post(s)
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right space-x-2 whitespace-nowrap font-medium">
                                <button type="button" @click="openEdit({{ json_encode($tag) }})" class="text-cyan font-bold hover:underline">
                                    Edit
                                </button>
                                
                                <form action="{{ route('admin.blog-tags.destroy', $tag->id) }}" method="POST" class="inline" onsubmit="return confirm('Delete tag #{{ $tag->name }}?');">
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
                            <td colspan="4" class="px-6 py-12 text-center text-gray-400">
                                No tags found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($tags->hasPages())
            <div class="px-6 py-4 border-t border-gray-200 bg-gray-50/50">
                {{ $tags->links() }}
            </div>
        @endif
    </div>

    <!-- Create Tag Modal -->
    <div x-show="createModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4">
        <div class="bg-white rounded-xl shadow-2xl max-w-sm w-full p-6 space-y-4" @click.away="createModal = false">
            <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                <h3 class="text-base font-bold text-navy">Create Tag</h3>
                <button type="button" @click="createModal = false" class="text-gray-400 hover:text-gray-600 text-lg">&times;</button>
            </div>
            <form action="{{ route('admin.blog-tags.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Tag Name *</label>
                    <input type="text" name="name" required class="w-full text-xs border-gray-300 rounded-lg focus:border-cyan focus:ring-cyan py-2" placeholder="e.g. AWS Certification">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Slug (optional)</label>
                    <input type="text" name="slug" placeholder="auto-generated" class="w-full text-xs border-gray-300 rounded-lg focus:border-cyan focus:ring-cyan py-2">
                </div>
                <div class="flex justify-end gap-2 pt-2 border-t border-gray-100">
                    <button type="button" @click="createModal = false" class="px-4 py-2 text-xs font-bold text-gray-600 hover:bg-gray-100 rounded-lg">Cancel</button>
                    <button type="submit" class="px-4 py-2 text-xs font-bold text-white bg-navy hover:bg-slate-800 rounded-lg shadow">Save Tag</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Tag Modal -->
    <div x-show="editModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4">
        <div class="bg-white rounded-xl shadow-2xl max-w-sm w-full p-6 space-y-4" @click.away="editModal = false">
            <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                <h3 class="text-base font-bold text-navy">Edit Tag</h3>
                <button type="button" @click="editModal = false" class="text-gray-400 hover:text-gray-600 text-lg">&times;</button>
            </div>
            <form :action="'{{ url('admin/blog-tags') }}/' + editId" method="POST" class="space-y-4">
                @csrf
                @method('PUT')
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Tag Name *</label>
                    <input type="text" name="name" x-model="editName" required class="w-full text-xs border-gray-300 rounded-lg focus:border-cyan focus:ring-cyan py-2">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Slug</label>
                    <input type="text" name="slug" x-model="editSlug" class="w-full text-xs border-gray-300 rounded-lg focus:border-cyan focus:ring-cyan py-2">
                </div>
                <div class="flex justify-end gap-2 pt-2 border-t border-gray-100">
                    <button type="button" @click="editModal = false" class="px-4 py-2 text-xs font-bold text-gray-600 hover:bg-gray-100 rounded-lg">Cancel</button>
                    <button type="submit" class="px-4 py-2 text-xs font-bold text-white bg-cyan hover:bg-cyan-dark rounded-lg shadow">Update Tag</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection