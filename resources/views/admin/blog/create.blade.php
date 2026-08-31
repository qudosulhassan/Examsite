@extends('layouts.admin')

@section('content')
<div class="max-w-5xl mx-auto space-y-6">
    <div class="flex justify-between items-center">
        <h1 class="text-2xl font-bold text-gray-800">Create Blog Post</h1>
        <a href="{{ route('admin.blog.index') }}" class="text-xs font-bold text-gray-500 hover:text-navy transition">
            &larr; Back to Posts
        </a>
    </div>

    @if ($errors->any())
        <div class="bg-red-50 border-l-4 border-red-500 p-4 rounded shadow-sm">
            <div class="font-bold text-red-800 text-sm">Please correct the following errors:</div>
            <ul class="list-disc list-inside text-xs mt-2 text-red-700">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('admin.blog.store') }}" method="POST" enctype="multipart/form-data" id="postForm" class="bg-white rounded-lg border border-gray-200 shadow-sm overflow-hidden">
        @csrf
        <div class="p-8 space-y-8">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Main Content Column -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Title -->
                    <div>
                        <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Post Title *</label>
                        <input type="text" name="title" id="title" value="{{ old('title') }}" required class="w-full text-base border-gray-300 rounded px-4 py-3 focus:border-cyan focus:ring-cyan transition-colors" placeholder="Enter an engaging title...">
                        @error('title')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- URL Slug -->
                    <div>
                        <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">URL Slug (Optional)</label>
                        <input type="text" name="slug" id="slug" value="{{ old('slug') }}" class="w-full text-sm font-mono border-gray-300 rounded px-4 py-3 focus:border-cyan focus:ring-cyan" placeholder="custom-url-slug">
                        <p class="text-xs text-gray-500 mt-1">Leave blank to auto-generate from the title.</p>
                        @error('slug')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Content (Tiptap) -->
                    <div class="tiptap-container" data-content="{{ base64_encode(old('content')) }}">
                        <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Post Content *</label>
                        
                        <!-- Toolbar -->
                        <div class="border border-gray-300 border-b-0 rounded-t-md bg-gray-50 p-2 flex flex-wrap gap-1 items-center">
                            <button type="button" class="btn-bold p-1.5 rounded text-gray-600 hover:bg-gray-200" title="Bold">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 4h8a4 4 0 014 4 4 4 0 01-4 4H6z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 12h9a4 4 0 014 4 4 4 0 01-4 4H6z"></path></svg>
                            </button>
                            <button type="button" class="btn-italic p-1.5 rounded text-gray-600 hover:bg-gray-200" title="Italic">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l-1.5 6m-5.5 2h5M8 4h5"></path></svg>
                            </button>
                            <div class="w-px h-6 bg-gray-300 mx-1"></div>
                            <button type="button" class="btn-p p-1.5 rounded font-bold text-sm text-gray-600 hover:bg-gray-200" title="Paragraph">P</button>
                            <button type="button" class="btn-h1 p-1.5 rounded font-bold text-sm text-gray-600 hover:bg-gray-200" title="Heading 1">H1</button>
                            <button type="button" class="btn-h2 p-1.5 rounded font-bold text-sm text-gray-600 hover:bg-gray-200" title="Heading 2">H2</button>
                            <button type="button" class="btn-h3 p-1.5 rounded font-bold text-sm text-gray-600 hover:bg-gray-200" title="Heading 3">H3</button>
                            <div class="w-px h-6 bg-gray-300 mx-1"></div>
                            <button type="button" class="btn-bullet p-1.5 rounded text-gray-600 hover:bg-gray-200" title="Bullet List">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                            </button>
                            <button type="button" class="btn-ordered p-1.5 rounded text-gray-600 hover:bg-gray-200" title="Numbered List">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H21M9 12H21M9 19H21M5 5.01V5M5 12.01V12M5 19.01V19"></path></svg>
                            </button>
                            <button type="button" class="btn-quote p-1.5 rounded text-gray-600 hover:bg-gray-200" title="Quote">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"></path></svg>
                            </button>
                            <div class="w-px h-6 bg-gray-300 mx-1"></div>
                            <button type="button" class="btn-link p-1.5 rounded text-gray-600 hover:bg-gray-200" title="Link">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path></svg>
                            </button>
                            <button type="button" class="btn-image p-1.5 rounded text-gray-600 hover:bg-gray-200" title="Image">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                            </button>
                        </div>
                        
                        <!-- Editor Body -->
                        <div class="editor-element w-full border border-gray-300 rounded-b-md focus-within:border-cyan focus-within:ring-1 focus-within:ring-cyan bg-white"></div>
                        
                        <!-- Hidden Input for Form Submission -->
                        <input type="hidden" name="content" class="content-input" value="{{ old('content') }}">

                        @error('content')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Excerpt -->
                    <div>
                        <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Excerpt (Brief Summary)</label>
                        <textarea name="excerpt" rows="3" class="w-full text-sm border-gray-300 rounded px-4 py-3 focus:border-cyan focus:ring-cyan transition-colors" placeholder="A short summary for blog lists and SEO...">{{ old('excerpt') }}</textarea>
                    </div>
                </div>

                <!-- Right Sidebar Settings -->
                <div class="space-y-6 bg-gray-50 p-6 rounded-lg border border-gray-100">
                    
                    <!-- Publishing Status -->
                    <div>
                        <label for="status" class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Publishing Status *</label>
                        <select name="status" id="status" required class="w-full text-sm border-gray-300 rounded bg-white focus:border-cyan focus:ring-cyan">
                            <option value="draft" {{ old('status') === 'draft' ? 'selected' : '' }}>Draft</option>
                            <option value="published" {{ old('status') === 'published' ? 'selected' : '' }}>Published</option>
                        </select>
                    </div>

                    <!-- Publish Date -->
                    <div>
                        <label for="published_at" class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Publish Date / Schedule</label>
                        <input type="datetime-local" name="published_at" id="published_at" value="{{ old('published_at', now()->format('Y-m-d\TH:i')) }}" class="w-full text-sm border-gray-300 rounded focus:border-cyan focus:ring-cyan">
                        <p class="text-xs text-gray-500 mt-1">Set to a future date to schedule publishing.</p>
                    </div>

                    <!-- Category -->
                    <div>
                        <label for="category_id" class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Category</label>
                        <select name="category_id" id="category_id" class="w-full text-sm border-gray-300 rounded bg-white focus:border-cyan focus:ring-cyan">
                            <option value="">-- Select Category --</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Author -->
                    <div>
                        <label for="user_id" class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Author</label>
                        <select name="user_id" id="user_id" class="w-full text-sm border-gray-300 rounded bg-white focus:border-cyan focus:ring-cyan">
                            @foreach($users as $user)
                                <option value="{{ $user->id }}" {{ old('user_id', auth()->id()) == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Tags -->
                    <div class="md:col-span-2">
                        <label for="tags" class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Tags</label>
                        <select name="tags[]" id="tags" multiple class="w-full text-sm border-gray-300 rounded focus:border-cyan focus:ring-cyan" placeholder="Select or type to create tags...">
                            @foreach($tags as $tag)
                                <option value="{{ $tag->id }}" {{ in_array($tag->id, old('tags', [])) ? 'selected' : '' }}>{{ $tag->name }}</option>
                            @endforeach
                        </select>
                        <p class="text-xs text-gray-500 mt-1">Type and press enter to create a new tag.</p>
                        @error('tags')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Related Exam -->
                    <div class="md:col-span-2">
                        <label for="related_exam_id" class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Related Exam (Optional)</label>
                        <select name="related_exam_id" id="related_exam_id" class="w-full text-sm border-gray-300 rounded focus:border-cyan focus:ring-cyan">
                            <option value="">-- None --</option>
                            @foreach($exams as $exam)
                                <option value="{{ $exam->id }}" {{ old('related_exam_id') == $exam->id ? 'selected' : '' }}>{{ $exam->exam_code }} - {{ $exam->title }}</option>
                            @endforeach
                        </select>
                        <p class="text-xs text-gray-500 mt-1">Linking an exam will show a promotional CTA on the blog post page.</p>
                        @error('related_exam_id')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Featured Image Upload -->
                    <div>
                        <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Featured Image</label>
                        <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md bg-white hover:bg-gray-50 transition relative overflow-hidden" id="image-drop-area">
                            <div class="space-y-1 text-center" id="upload-prompt">
                                <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48" aria-hidden="true">
                                    <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                                <div class="flex text-sm text-gray-600 justify-center">
                                    <label for="featured_image" class="relative cursor-pointer rounded-md font-medium text-cyan hover:text-cyan-dark focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-cyan">
                                        <span>Upload a file</span>
                                        <input id="featured_image" name="featured_image" type="file" class="sr-only" accept="image/*">
                                    </label>
                                </div>
                                <p class="text-xs text-gray-500">PNG, JPG, WEBP up to 2MB</p>
                            </div>
                            <img id="image-preview" class="hidden absolute inset-0 w-full h-full object-cover">
                        </div>
                    </div>

                    <!-- Featured Toggle -->
                    <div class="flex items-center pt-2">
                        <input id="is_featured" name="is_featured" type="checkbox" value="1" {{ old('is_featured') ? 'checked' : '' }} class="h-4 w-4 text-cyan focus:ring-cyan border-gray-300 rounded">
                        <label for="is_featured" class="ml-2 block text-sm text-gray-700 font-medium">
                            Featured Post
                        </label>
                    </div>
                </div>
            </div>

            <!-- SEO Settings -->
            <div class="border-t border-gray-100 pt-8 mt-8">
                <h3 class="text-sm font-bold text-navy mb-4 uppercase tracking-wider">SEO Configuration</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="meta_title" class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Meta Title</label>
                        <input type="text" name="meta_title" id="meta_title" value="{{ old('meta_title') }}" class="w-full text-sm border-gray-300 rounded focus:border-cyan focus:ring-cyan">
                    </div>
                    <div>
                        <label for="meta_keywords" class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Meta Keywords</label>
                        <input type="text" name="meta_keywords" id="meta_keywords" value="{{ old('meta_keywords') }}" placeholder="tech, news, exams" class="w-full text-sm border-gray-300 rounded focus:border-cyan focus:ring-cyan">
                    </div>
                    <div class="md:col-span-2">
                        <label for="meta_description" class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Meta Description</label>
                        <textarea name="meta_description" id="meta_description" rows="2" class="w-full text-sm border-gray-300 rounded focus:border-cyan focus:ring-cyan">{{ old('meta_description') }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-8 flex justify-end px-8 pb-8">
            <button type="submit" class="bg-navy hover:bg-opacity-90 text-white text-sm font-bold py-3 px-8 rounded shadow-md transition transform hover:-translate-y-0.5">
                Publish Post
            </button>
        </div>
    </form>
</div>
@endsection

@section('scripts')
<script type="module">
    // Initialize TomSelect for Categories
    if (window.TomSelect) {
        new TomSelect('#category_id', {
            create: true,
            sortField: {
                field: "text",
                direction: "asc"
            },
            placeholder: 'Select or create...',
        });

        // Initialize TomSelect for Tags
        new TomSelect('#tags',{
            create: true,
            maxItems: 10,
            placeholder: 'Add tags...',
        });
    }

    // Image Upload Preview
    const fileInput = document.getElementById('featured_image');
    const preview = document.getElementById('image-preview');
    const prompt = document.getElementById('upload-prompt');

    if (fileInput) {
        fileInput.addEventListener('change', function() {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    preview.classList.remove('hidden');
                    prompt.classList.add('hidden');
                }
                reader.readAsDataURL(file);
            }
        });
    }
</script>
@endsection
