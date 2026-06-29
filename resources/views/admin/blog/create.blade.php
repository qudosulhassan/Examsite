@extends('layouts.admin')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="flex justify-between items-center">
        <h1 class="text-2xl font-bold text-gray-800">Create Blog Post</h1>
        <a href="{{ route('admin.blog.index') }}" class="text-xs font-bold text-gray-500 hover:text-navy transition">
            ← Back to Posts
        </a>
    </div>

    @if ($errors->any())
        <div class="bg-red-150 border-l-4 border-red-500 text-red-700 p-4 rounded shadow-sm">
            <div class="font-bold">Please correct the following errors:</div>
            <ul class="list-disc list-inside text-xs mt-2">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="bg-white rounded-lg border border-gray-250 p-6 shadow-sm">
        <form action="{{ route('admin.blog.store') }}" method="POST" id="postForm" class="space-y-6">
            @csrf

            <!-- Title -->
            <div>
                <label for="title" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Post Title *</label>
                <input type="text" name="title" id="title" value="{{ old('title') }}" required class="w-full text-sm border-gray-300 rounded focus:border-cyan focus:ring-cyan">
            </div>

            <!-- Excerpt -->
            <div>
                <label for="excerpt" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Excerpt (Brief Summary)</label>
                <textarea name="excerpt" id="excerpt" rows="2" class="w-full text-sm border-gray-300 rounded focus:border-cyan focus:ring-cyan">{{ old('excerpt') }}</textarea>
            </div>

            <!-- Content (Quill Editor) -->
            <div>
                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Content *</label>
                <input type="hidden" name="content" id="content">
                <div id="editor" class="h-64 bg-white rounded border border-gray-300">
                    {!! old('content') !!}
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Featured Image URL -->
                <div>
                    <label for="featured_image" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Featured Image URL</label>
                    <input type="text" name="featured_image" id="featured_image" value="{{ old('featured_image') }}" class="w-full text-sm border-gray-300 rounded focus:border-cyan focus:ring-cyan">
                </div>

                <!-- Status -->
                <div>
                    <label for="status" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Publishing Status *</label>
                    <select name="status" id="status" required class="w-full text-sm border-gray-300 rounded focus:border-cyan focus:ring-cyan">
                        <option value="draft" {{ old('status') === 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="published" {{ old('status') === 'published' ? 'selected' : '' }}>Published</option>
                    </select>
                </div>
            </div>

            <!-- SEO Meta Tags Section Header -->
            <div class="border-t border-gray-200 pt-6">
                <h3 class="text-sm font-bold text-navy mb-4">SEO Configuration (Optional)</h3>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Meta Title -->
                <div>
                    <label for="meta_title" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Meta Title</label>
                    <input type="text" name="meta_title" id="meta_title" value="{{ old('meta_title') }}" class="w-full text-sm border-gray-300 rounded focus:border-cyan focus:ring-cyan">
                </div>

                <!-- Meta Description -->
                <div>
                    <label for="meta_description" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Meta Description</label>
                    <input type="text" name="meta_description" id="meta_description" value="{{ old('meta_description') }}" class="w-full text-sm border-gray-300 rounded focus:border-cyan focus:ring-cyan">
                </div>
            </div>
            
            <div class="mt-4">
                <label for="meta_keywords" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Meta Keywords (Comma-separated)</label>
                <input type="text" name="meta_keywords" id="meta_keywords" value="{{ old('meta_keywords') }}" placeholder="e.g., tech news, exam tips, certification guide" class="w-full text-sm border-gray-300 rounded focus:border-cyan focus:ring-cyan">
            </div>

            <div class="flex justify-end pt-4">
                <button type="submit" class="bg-navy hover:bg-opacity-95 text-white text-sm font-bold py-2 px-6 rounded shadow transition">
                    Create Post
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var quill = new Quill('#editor', {
            theme: 'snow',
            modules: {
                toolbar: [
                    [{ 'header': [1, 2, 3, false] }],
                    ['bold', 'italic', 'underline', 'strike'],
                    ['blockquote', 'code-block'],
                    [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                    ['link', 'image'],
                    ['clean']
                ]
            }
        });

        // Update hidden field on form submit
        var form = document.getElementById('postForm');
        form.onsubmit = function() {
            var content = document.querySelector('input[name=content]');
            content.value = quill.root.innerHTML;
            return true;
        };
    });
</script>
@endsection
