@extends('layouts.admin')

@section('content')
<div class="space-y-6" x-data="{
    title: {{ json_encode(old('title', $post->title ?? '')) }},
    slug: {{ json_encode(old('slug', $post->slug ?? '')) }},
    autoSlug: {{ isset($post) ? 'false' : 'true' }},
    status: {{ json_encode(old('status', $post->status ?? 'draft')) }},
    publishedAt: {{ json_encode(old('published_at', isset($post) && $post->published_at ? $post->published_at->format('Y-m-d\TH:i') : now()->format('Y-m-d\TH:i'))) }},
    excerpt: {{ json_encode(old('excerpt', $post->excerpt ?? '')) }},
    metaTitle: {{ json_encode(old('meta_title', $post->meta_title ?? '')) }},
    metaDesc: {{ json_encode(old('meta_description', $post->meta_description ?? '')) }},
    canonicalUrl: {{ json_encode(old('canonical_url', $post->canonical_url ?? '')) }},
    ogTitle: {{ json_encode(old('og_title', $post->og_title ?? '')) }},
    ogDesc: {{ json_encode(old('og_description', $post->og_description ?? '')) }},
    featuredImage: {{ json_encode(old('featured_image', $post->featured_image ?? '')) }},
    featuredImageAlt: {{ json_encode(old('featured_image_alt', $post->featured_image_alt ?? '')) }},
    categoryModal: false,
    newCatName: '',
    newCatDesc: '',
    mediaModal: false,
    mediaList: [],
    loadingMedia: false,
    mediaPage: 1,

    updateSlug() {
        if (this.autoSlug) {
            this.slug = this.title.toLowerCase().trim()
                .replace(/[^a-z0-9\s-]/g, '')
                .replace(/[\s-]+/g, '-');
        }
    },

    get qualityScore() {
        let score = 0;
        if (this.title && this.title.length >= 10) score += 20;
        if (this.excerpt && this.excerpt.length >= 30) score += 15;
        if (this.metaTitle || this.title) score += 15;
        if (this.metaDesc && this.metaDesc.length >= 50) score += 20;
        if (this.featuredImage) score += 15;
        let catEl = document.getElementById('category_id');
        if (catEl && catEl.value) score += 15;
        return Math.min(score, 100);
    },

    openMediaGallery() {
        this.mediaModal = true;
        if (this.mediaList.length === 0) {
            this.fetchMedia();
        }
    },

    fetchMedia() {
        this.loadingMedia = true;
        fetch('{{ route('admin.media.index') }}', { credentials: 'same-origin', headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } })
            .then(res => res.json())
            .then(data => {
                this.mediaList = data.data || [];
                this.loadingMedia = false;
            })
            .catch(() => {
                this.loadingMedia = false;
            });
    },

    selectMedia(url) {
        this.featuredImage = url;
        this.mediaModal = false;
    },

    submitQuickCategory() {
        if (!this.newCatName.trim()) return;
        fetch('{{ route('admin.blog.quick-category') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                name: this.newCatName,
                description: this.newCatDesc
            })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                const select = document.getElementById('category_id');
                const opt = document.createElement('option');
                opt.value = data.category.id;
                opt.text = data.category.name;
                opt.selected = true;
                select.appendChild(opt);
                this.categoryModal = false;
                this.newCatName = '';
                this.newCatDesc = '';
                alert('Category created and selected!');
            } else {
                alert('Error creating category.');
            }
        })
        .catch(err => {
            alert('Failed to create category.');
        });
    }
}">

    <!-- Top Navigation & Title Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-2 border-b border-gray-200">
        <div>
            <div class="flex items-center gap-2">
                <a href="{{ route('admin.blog.index') }}" class="text-xs text-gray-500 hover:text-navy">&larr; Back to Posts</a>
                <span class="text-gray-300">/</span>
                <span class="text-xs text-cyan font-bold">{{ isset($post) ? 'Edit Post #' . $post->id : 'New Post' }}</span>
            </div>
            <h1 class="text-2xl font-black text-navy tracking-tight mt-1">
                {{ isset($post) ? 'Edit: ' . $post->title : 'Create New Article' }}
            </h1>
        </div>

        <div class="flex items-center gap-3">
            @if(isset($post) && $post->status === 'published')
                <a href="{{ route('blog.show', $post->slug) }}" target="_blank" class="inline-flex items-center px-3.5 py-2 border border-gray-300 rounded-lg text-xs font-bold text-gray-700 bg-white hover:bg-gray-50 shadow-sm transition">
                    <svg class="w-3.5 h-3.5 mr-1.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" /></svg>
                    Live Preview
                </a>
            @endif

            @if(isset($post))
                <form action="{{ route('admin.blog.duplicate', $post->id) }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="inline-flex items-center px-3.5 py-2 border border-gray-300 rounded-lg text-xs font-bold text-gray-700 bg-white hover:bg-gray-50 shadow-sm transition">
                        Duplicate
                    </button>
                </form>
            @endif
        </div>
    </div>

    @if ($errors->any())
        <div class="bg-rose-50 border-l-4 border-rose-500 p-4 rounded-r-lg shadow-sm">
            <div class="font-bold text-rose-800 text-xs uppercase tracking-wider">Please correct the following errors:</div>
            <ul class="list-disc list-inside text-xs mt-2 text-rose-700 space-y-0.5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ isset($post) ? route('admin.blog.update', $post->id) : route('admin.blog.store') }}" method="POST" enctype="multipart/form-data" id="postForm" class="space-y-6">
        @csrf
        @if(isset($post))
            @method('PUT')
        @endif

        <input type="hidden" name="status" :value="status">

        <!-- 70/30 Grid Layout -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
            
            <!-- LEFT COLUMN (70%): Primary Content & SEO Canvas -->
            <div class="lg:col-span-8 space-y-6">

                <!-- Post Title & Slug Card -->
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 space-y-4">
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label class="block text-xs font-bold text-navy uppercase tracking-wider">Post Title *</label>
                            <span class="text-[11px] text-gray-400" x-text="(title ? title.length : 0) + ' / 70 chars'"></span>
                        </div>
                        <input type="text" name="title" id="title" x-model="title" @input="updateSlug()" required
                               placeholder="e.g. Complete 2026 Guide to Passing the AWS Solutions Architect Exam"
                               class="w-full text-base font-bold text-navy border-gray-300 rounded-lg px-4 py-3 focus:border-cyan focus:ring-cyan transition">
                    </div>

                    <!-- Slug with Auto-Sync & Safeguard -->
                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <label class="block text-[11px] font-bold text-gray-500 uppercase tracking-wider">URL Permalink</label>
                            <button type="button" @click="autoSlug = !autoSlug; if(autoSlug) updateSlug();" class="text-[11px] text-cyan font-bold hover:underline" x-text="autoSlug ? 'Switch to Custom Slug' : 'Auto-Sync from Title'"></button>
                        </div>
                        <div class="flex items-center rounded-lg border border-gray-300 focus-within:border-cyan focus-within:ring-1 focus-within:ring-cyan bg-gray-50 overflow-hidden">
                            <span class="px-3 text-xs font-mono text-gray-400 select-none">/blog/</span>
                            <input type="text" name="slug" id="slug" x-model="slug" :readonly="autoSlug"
                                   class="w-full text-xs font-mono border-0 bg-transparent py-2.5 px-0 text-navy focus:ring-0">
                        </div>
                        @if(isset($post))
                            <p class="text-[11px] text-gray-400 mt-1 flex items-center gap-1">
                                <svg class="w-3.5 h-3.5 text-cyan" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                301 Redirect Safeguard: If you modify this slug, existing visits to the old URL will automatically redirect here.
                            </p>
                        @endif
                    </div>
                </div>

                <!-- Rich Text Editor Card -->
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 space-y-4">
                    <div class="flex items-center justify-between">
                        <label class="block text-xs font-bold text-navy uppercase tracking-wider">Article Content *</label>
                        <span class="text-[11px] text-gray-400">TipTap Rich Text Editor</span>
                    </div>

                    <div class="tiptap-container" data-content="{{ base64_encode(old('content', $post->content ?? '')) }}">
                        <!-- Toolbar -->
                        <div class="border border-gray-200 border-b-0 rounded-t-lg bg-gray-50/80 p-2 flex flex-wrap gap-1 items-center">
                            <button type="button" class="btn-bold p-1.5 rounded text-gray-600 hover:bg-gray-200 font-bold" title="Bold">B</button>
                            <button type="button" class="btn-italic p-1.5 rounded text-gray-600 hover:bg-gray-200 italic" title="Italic">I</button>
                            <div class="w-px h-5 bg-gray-300 mx-1"></div>
                            <button type="button" class="btn-p p-1.5 rounded text-xs font-bold text-gray-600 hover:bg-gray-200" title="Paragraph">Paragraph</button>
                            <button type="button" class="btn-h1 p-1.5 rounded text-xs font-bold text-gray-600 hover:bg-gray-200" title="Heading 1">H1</button>
                            <button type="button" class="btn-h2 p-1.5 rounded text-xs font-bold text-gray-600 hover:bg-gray-200" title="Heading 2">H2</button>
                            <button type="button" class="btn-h3 p-1.5 rounded text-xs font-bold text-gray-600 hover:bg-gray-200" title="Heading 3">H3</button>
                            <div class="w-px h-5 bg-gray-300 mx-1"></div>
                            <button type="button" class="btn-bullet p-1.5 rounded text-gray-600 hover:bg-gray-200" title="Bullet List">• List</button>
                            <button type="button" class="btn-ordered p-1.5 rounded text-gray-600 hover:bg-gray-200" title="Numbered List">1. List</button>
                            <button type="button" class="btn-quote p-1.5 rounded text-gray-600 hover:bg-gray-200" title="Quote">Quote</button>
                            <div class="w-px h-5 bg-gray-300 mx-1"></div>
                            <button type="button" class="btn-link p-1.5 rounded text-gray-600 hover:bg-gray-200" title="Insert Link">Link</button>
                            <button type="button" class="btn-image p-1.5 rounded text-gray-600 hover:bg-gray-200" title="Upload Inline Image">Image</button>
                        </div>
                        
                        <!-- Editor Canvas -->
                        <div class="editor-element w-full border border-gray-200 rounded-b-lg focus-within:border-cyan focus-within:ring-1 focus-within:ring-cyan bg-white min-h-[350px]"></div>
                        <input type="hidden" name="content" class="content-input" value="{{ old('content', $post->content ?? '') }}">
                    </div>
                </div>

                <!-- Excerpt Card -->
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 space-y-3">
                    <div class="flex items-center justify-between">
                        <label class="block text-xs font-bold text-navy uppercase tracking-wider">Post Excerpt</label>
                        <span class="text-[11px] text-gray-400" x-text="(excerpt ? excerpt.length : 0) + ' / 160 chars'"></span>
                    </div>
                    <textarea name="excerpt" id="excerpt" rows="3" x-model="excerpt"
                              placeholder="Provide a concise 1-2 sentence preview to display in article grids and social cards..."
                              class="w-full text-xs text-gray-700 border-gray-300 rounded-lg p-3 focus:border-cyan focus:ring-cyan">{{ old('excerpt', $post->excerpt ?? '') }}</textarea>
                </div>

                <!-- SEO & SERP Preview Card -->
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 space-y-6">
                    <div class="border-b border-gray-100 pb-3 flex items-center justify-between">
                        <div>
                            <h3 class="text-xs font-bold text-navy uppercase tracking-wider">Search Engine Optimization (SEO)</h3>
                            <p class="text-[11px] text-gray-400">Configure search snippets and search crawler directives.</p>
                        </div>
                        <span class="text-[10px] font-bold uppercase bg-emerald-100 text-emerald-800 px-2 py-0.5 rounded-full">Google Snippet Preview</span>
                    </div>

                    <!-- Live Google SERP Box -->
                    <div class="bg-slate-50 border border-slate-200 rounded-xl p-4 space-y-1">
                        <div class="text-[11px] text-gray-500 font-mono flex items-center gap-1.5">
                            <span class="w-4 h-4 rounded-full bg-navy text-white text-[9px] font-bold flex items-center justify-center">E</span>
                            <span>{{ url('/blog') }}/<span x-text="slug || 'article-slug'"></span></span>
                        </div>
                        <h4 class="text-base text-blue-700 hover:underline font-medium cursor-pointer"
                            x-text="metaTitle || title || 'Post Title - ExamTopicsBase'"></h4>
                        <p class="text-xs text-gray-600 line-clamp-2 leading-relaxed"
                           x-text="metaDesc || excerpt || 'Detailed guide and expert preparation insights for ExamTopicsBase certification candidates...'"></p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="md:col-span-2">
                            <label class="block text-xs font-bold text-gray-700 uppercase mb-1">SEO Meta Title</label>
                            <input type="text" name="meta_title" x-model="metaTitle" placeholder="Defaults to post title if left blank"
                                   class="w-full text-xs border-gray-300 rounded-lg p-2.5 focus:border-cyan focus:ring-cyan">
                        </div>

                        <div class="md:col-span-2">
                            <div class="flex items-center justify-between mb-1">
                                <label class="block text-xs font-bold text-gray-700 uppercase">Meta Description</label>
                                <span class="text-[11px] text-gray-400" x-text="(metaDesc ? metaDesc.length : 0) + ' / 160 recommended'"></span>
                            </div>
                            <textarea name="meta_description" rows="2" x-model="metaDesc" placeholder="Concise summary for search engines (150-160 characters)..."
                                      class="w-full text-xs border-gray-300 rounded-lg p-2.5 focus:border-cyan focus:ring-cyan">{{ old('meta_description', $post->meta_description ?? '') }}</textarea>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Meta Keywords</label>
                            <input type="text" name="meta_keywords" value="{{ old('meta_keywords', $post->meta_keywords ?? '') }}" placeholder="aws, solutions architect, exam dumps"
                                   class="w-full text-xs border-gray-300 rounded-lg p-2.5 focus:border-cyan focus:ring-cyan">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Canonical URL Override</label>
                            <input type="url" name="canonical_url" x-model="canonicalUrl" placeholder="Defaults to current blog URL"
                                   class="w-full text-xs border-gray-300 rounded-lg p-2.5 focus:border-cyan focus:ring-cyan">
                        </div>
                    </div>
                </div>

                <!-- Social Graph (Open Graph) Card -->
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 space-y-4">
                    <div class="border-b border-gray-100 pb-3">
                        <h3 class="text-xs font-bold text-navy uppercase tracking-wider">Social Graph & Open Graph (OG)</h3>
                        <p class="text-[11px] text-gray-400">Optimize how links appear when shared across LinkedIn, Twitter/X, and Facebook.</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase mb-1">OG Title</label>
                            <input type="text" name="og_title" x-model="ogTitle" placeholder="Defaults to post title"
                                   class="w-full text-xs border-gray-300 rounded-lg p-2.5 focus:border-cyan focus:ring-cyan">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase mb-1">OG Social Image URL</label>
                            <input type="text" name="og_image" :value="featuredImage" readonly placeholder="Uses featured image"
                                   class="w-full text-xs border-gray-300 rounded-lg p-2.5 bg-gray-50 focus:border-cyan focus:ring-cyan">
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-xs font-bold text-gray-700 uppercase mb-1">OG Description</label>
                            <textarea name="og_description" rows="2" x-model="ogDesc" placeholder="Defaults to excerpt"
                                      class="w-full text-xs border-gray-300 rounded-lg p-2.5 focus:border-cyan focus:ring-cyan">{{ old('og_description', $post->og_description ?? '') }}</textarea>
                        </div>
                    </div>
                </div>

            </div>

            <!-- RIGHT COLUMN (30%): Sticky Publishing & Metadata Sidebar -->
            <div class="lg:col-span-4 space-y-6 sticky top-20">

                <!-- Publishing Panel -->
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 space-y-5">
                    <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                        <h3 class="text-xs font-bold text-navy uppercase tracking-wider">Publishing</h3>
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-black uppercase"
                              :class="{
                                  'bg-emerald-100 text-emerald-800': status === 'published',
                                  'bg-amber-100 text-amber-800': status === 'draft',
                                  'bg-blue-100 text-blue-800': status === 'scheduled'
                              }" x-text="status"></span>
                    </div>

                    <!-- Status Selection Buttons -->
                    <div class="grid grid-cols-3 gap-2">
                        <button type="button" @click="status = 'draft'"
                                class="py-2 text-xs font-bold rounded-lg border text-center transition"
                                :class="status === 'draft' ? 'bg-amber-500 text-white border-amber-500 shadow-sm' : 'bg-gray-50 text-gray-600 border-gray-200 hover:bg-gray-100'">
                            Draft
                        </button>
                        <button type="button" @click="status = 'published'"
                                class="py-2 text-xs font-bold rounded-lg border text-center transition"
                                :class="status === 'published' ? 'bg-emerald-600 text-white border-emerald-600 shadow-sm' : 'bg-gray-50 text-gray-600 border-gray-200 hover:bg-gray-100'">
                            Published
                        </button>
                        <button type="button" @click="status = 'scheduled'"
                                class="py-2 text-xs font-bold rounded-lg border text-center transition"
                                :class="status === 'scheduled' ? 'bg-blue-600 text-white border-blue-600 shadow-sm' : 'bg-gray-50 text-gray-600 border-gray-200 hover:bg-gray-100'">
                            Schedule
                        </button>
                    </div>

                    <!-- Schedule Date Picker -->
                    <div x-show="status === 'scheduled' || status === 'published'" x-transition class="space-y-1">
                        <label class="block text-[11px] font-bold text-gray-500 uppercase tracking-wider" x-text="status === 'scheduled' ? 'Schedule Release At:' : 'Published Date:'"></label>
                        <input type="datetime-local" name="published_at" x-model="publishedAt"
                               class="w-full text-xs border-gray-300 rounded-lg p-2.5 focus:border-cyan focus:ring-cyan">
                    </div>

                    <!-- Featured Flag Toggle -->
                    <div class="pt-2 border-t border-gray-100">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="is_featured" value="1" {{ old('is_featured', $post->is_featured ?? false) ? 'checked' : '' }}
                                   class="rounded border-gray-300 text-cyan focus:ring-cyan h-4 w-4">
                            <span class="text-xs font-bold text-gray-700">Mark as Featured Post</span>
                        </label>
                        <p class="text-[10px] text-gray-400 mt-0.5">Featured articles are pinned to the header carousel on the public blog.</p>
                    </div>

                    <!-- Action Submit Buttons -->
                    <div class="space-y-2 pt-2 border-t border-gray-100">
                        <button type="submit" class="w-full bg-navy hover:bg-slate-800 text-white text-xs font-bold py-3 px-4 rounded-lg shadow-md hover:shadow transition transform hover:-translate-y-0.5">
                            {{ isset($post) ? 'Update Blog Post' : 'Save & Publish Post' }}
                        </button>

                        <button type="button" @click="status = 'draft'; $nextTick(() => document.getElementById('postForm').submit());"
                                class="w-full bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-bold py-2.5 px-4 rounded-lg transition">
                            Save as Draft
                        </button>
                    </div>
                </div>

                <!-- Content Quality Score Card -->
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 space-y-3">
                    <div class="flex items-center justify-between">
                        <h3 class="text-xs font-bold text-navy uppercase tracking-wider">Content Quality Score</h3>
                        <span class="text-xs font-black text-navy" x-text="qualityScore + '%'"></span>
                    </div>

                    <div class="w-full bg-gray-100 rounded-full h-2 overflow-hidden">
                        <div class="h-2 rounded-full transition-all duration-500"
                             :class="{
                                 'bg-rose-500': qualityScore < 40,
                                 'bg-amber-500': qualityScore >= 40 && qualityScore < 75,
                                 'bg-emerald-500': qualityScore >= 75
                             }" :style="'width: ' + qualityScore + '%'"></div>
                    </div>

                    <ul class="text-[11px] space-y-1.5 pt-2 text-gray-500">
                        <li class="flex items-center gap-1.5" :class="title && title.length >= 10 ? 'text-emerald-600 font-semibold' : ''">
                            <span x-text="title && title.length >= 10 ? '✓' : '○'"></span> Engaging Title (10+ chars)
                        </li>
                        <li class="flex items-center gap-1.5" :class="excerpt && excerpt.length >= 30 ? 'text-emerald-600 font-semibold' : ''">
                            <span x-text="excerpt && excerpt.length >= 30 ? '✓' : '○'"></span> Summary Excerpt (30+ chars)
                        </li>
                        <li class="flex items-center gap-1.5" :class="metaDesc && metaDesc.length >= 50 ? 'text-emerald-600 font-semibold' : ''">
                            <span x-text="metaDesc && metaDesc.length >= 50 ? '✓' : '○'"></span> SEO Meta Description (50+ chars)
                        </li>
                        <li class="flex items-center gap-1.5" :class="featuredImage ? 'text-emerald-600 font-semibold' : ''">
                            <span x-text="featuredImage ? '✓' : '○'"></span> Featured Thumbnail Image
                        </li>
                    </ul>
                </div>

                <!-- Category Selector + Quick Create Modal Trigger -->
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 space-y-3">
                    <div class="flex items-center justify-between">
                        <label for="category_id" class="block text-xs font-bold text-navy uppercase tracking-wider">Category</label>
                        <button type="button" @click="categoryModal = true" class="text-[11px] text-cyan font-bold hover:underline">
                            + Create New
                        </button>
                    </div>

                    <select name="category_id" id="category_id" class="w-full text-xs border-gray-300 rounded-lg p-2.5 focus:border-cyan focus:ring-cyan bg-white">
                        <option value="">-- Unassigned Category --</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ old('category_id', $post->category_id ?? '') == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Tags Manager Chips -->
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 space-y-3">
                    <label class="block text-xs font-bold text-navy uppercase tracking-wider">Tags</label>
                    <select name="tags[]" id="tags" multiple class="w-full text-xs border-gray-300 rounded-lg focus:border-cyan focus:ring-cyan" placeholder="Select or type tags...">
                        @foreach($tags as $tag)
                            <option value="{{ $tag->id }}" {{ (collect(old('tags', isset($post) ? $post->tags->pluck('id')->toArray() : []))->contains($tag->id)) ? 'selected' : '' }}>
                                {{ $tag->name }}
                            </option>
                        @endforeach
                    </select>
                    <p class="text-[10px] text-gray-400">Type a new tag and hit Enter to add.</p>
                </div>

                <!-- Author Selector -->
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 space-y-3">
                    <label for="user_id" class="block text-xs font-bold text-navy uppercase tracking-wider">Author</label>
                    <select name="user_id" id="user_id" class="w-full text-xs border-gray-300 rounded-lg p-2.5 focus:border-cyan focus:ring-cyan bg-white">
                        @foreach($users as $user)
                            <option value="{{ $user->id }}" {{ old('user_id', $post->user_id ?? auth()->id()) == $user->id ? 'selected' : '' }}>
                                {{ $user->name }} ({{ $user->email }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Related Exam Linker (Promotional CTA) -->
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 space-y-3">
                    <label for="related_exam_id" class="block text-xs font-bold text-navy uppercase tracking-wider">Related Exam (Optional)</label>
                    <select name="related_exam_id" id="related_exam_id" class="w-full text-xs border-gray-300 rounded-lg p-2.5 focus:border-cyan focus:ring-cyan bg-white">
                        <option value="">-- No Exam Link --</option>
                        @foreach($exams as $exam)
                            <option value="{{ $exam->id }}" {{ old('related_exam_id', $post->related_exam_id ?? '') == $exam->id ? 'selected' : '' }}>
                                [{{ $exam->exam_code }}] {{ $exam->vendor ? $exam->vendor->name . ' - ' : '' }}{{ $exam->exam_name }}
                            </option>
                        @endforeach
                    </select>
                    <p class="text-[10px] text-gray-400">Displays high-converting practice test CTA card inside the blog article.</p>
                </div>

                <!-- Featured Image & Media Gallery Browser -->
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 space-y-4">
                    <div class="flex items-center justify-between">
                        <label class="block text-xs font-bold text-navy uppercase tracking-wider">Featured Image</label>
                        <button type="button" @click="openMediaGallery()" class="text-[11px] text-cyan font-bold hover:underline">
                            Media Gallery
                        </button>
                    </div>

                    <!-- Preview Thumbnail -->
                    <div class="relative rounded-lg overflow-hidden border border-gray-200 bg-gray-50 h-40 flex items-center justify-center">
                        <template x-if="featuredImage">
                            <div class="w-full h-full relative group">
                                <img :src="featuredImage" class="w-full h-full object-cover">
                                <button type="button" @click="featuredImage = ''" class="absolute top-2 right-2 bg-black/70 text-white rounded-full p-1 text-xs hover:bg-black">
                                    ✕
                                </button>
                            </div>
                        </template>
                        <template x-if="!featuredImage">
                            <div class="text-center p-4">
                                <svg class="mx-auto h-8 w-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                <p class="text-[11px] text-gray-400 mt-1">No featured image selected</p>
                            </div>
                        </template>
                    </div>

                    <input type="hidden" name="featured_image" x-model="featuredImage">

                    <div>
                        <label class="block text-[11px] font-bold text-gray-500 uppercase mb-1">Or Upload File from Device</label>
                        <input type="file" name="featured_image_file" accept="image/*"
                               class="w-full text-xs text-gray-500 file:mr-2 file:py-1.5 file:px-3 file:rounded-md file:border-0 file:text-xs file:font-bold file:bg-navy/10 file:text-navy hover:file:bg-navy/20">
                    </div>

                    <div>
                        <label class="block text-[11px] font-bold text-gray-500 uppercase mb-1">Image Alt Text (Accessibility & SEO)</label>
                        <input type="text" name="featured_image_alt" x-model="featuredImageAlt" placeholder="Brief description of the image..."
                               class="w-full text-xs border-gray-300 rounded-lg p-2 focus:border-cyan focus:ring-cyan">
                    </div>
                </div>

            </div>
        </div>
    </form>

    <!-- Modal 1: Quick Create Category -->
    <div x-show="categoryModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4">
        <div class="bg-white rounded-xl shadow-2xl max-w-sm w-full p-6 space-y-4" @click.away="categoryModal = false">
            <div class="flex items-center justify-between border-b border-gray-100 pb-2">
                <h3 class="text-sm font-bold text-navy">Quick Create Category</h3>
                <button type="button" @click="categoryModal = false" class="text-gray-400 hover:text-gray-600 text-base">&times;</button>
            </div>
            <div class="space-y-3 text-xs">
                <div>
                    <label class="block font-bold text-gray-700 uppercase mb-1">Category Name *</label>
                    <input type="text" x-model="newCatName" class="w-full text-xs border-gray-300 rounded-lg p-2 focus:border-cyan focus:ring-cyan" placeholder="e.g. Cloud Security">
                </div>
                <div>
                    <label class="block font-bold text-gray-700 uppercase mb-1">Description</label>
                    <textarea x-model="newCatDesc" rows="2" class="w-full text-xs border-gray-300 rounded-lg p-2 focus:border-cyan focus:ring-cyan" placeholder="Optional notes..."></textarea>
                </div>
            </div>
            <div class="flex justify-end gap-2 pt-2 border-t border-gray-100">
                <button type="button" @click="categoryModal = false" class="px-3 py-1.5 text-xs font-bold text-gray-600 hover:bg-gray-100 rounded-lg">Cancel</button>
                <button type="button" @click="submitQuickCategory()" class="px-4 py-1.5 text-xs font-bold text-white bg-navy hover:bg-slate-800 rounded-lg shadow">Create Category</button>
            </div>
        </div>
    </div>

    <!-- Modal 2: Media Gallery Picker Drawer -->
    <div x-show="mediaModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4">
        <div class="bg-white rounded-xl shadow-2xl max-w-3xl w-full p-6 space-y-4" @click.away="mediaModal = false">
            <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                <div>
                    <h3 class="text-base font-bold text-navy">Select from Media Gallery</h3>
                    <p class="text-xs text-gray-400">Click any image to set it as the article's featured thumbnail.</p>
                </div>
                <button type="button" @click="mediaModal = false" class="text-gray-400 hover:text-gray-600 text-xl">&times;</button>
            </div>

            <div class="max-h-96 overflow-y-auto p-1">
                <template x-if="loadingMedia">
                    <div class="text-center py-12 text-xs text-gray-400">
                        Loading media gallery items...
                    </div>
                </template>

                <template x-if="!loadingMedia && mediaList.length === 0">
                    <div class="text-center py-12 text-xs text-gray-400">
                        No images uploaded in Media Gallery yet. You can upload via the file picker.
                    </div>
                </template>

                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3">
                    <template x-for="item in mediaList" :key="item.id">
                        <div class="group relative rounded-lg overflow-hidden border border-gray-200 aspect-video bg-gray-50 cursor-pointer hover:border-cyan transition"
                             @click="selectMedia(item.url || ('/storage/' + item.path))">
                            <img :src="item.url || ('/storage/' + item.path)" class="w-full h-full object-cover">
                            <div class="absolute inset-0 bg-navy/60 opacity-0 group-hover:opacity-100 transition flex items-center justify-center">
                                <span class="text-white text-xs font-bold bg-cyan px-2.5 py-1 rounded-md shadow">Choose</span>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            <div class="flex justify-end pt-2 border-t border-gray-100">
                <button type="button" @click="mediaModal = false" class="px-4 py-2 text-xs font-bold text-gray-600 hover:bg-gray-100 rounded-lg">Close</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script type="module">
    if (window.TomSelect) {
        new TomSelect('#tags', {
            create: true,
            maxItems: 12,
            placeholder: 'Type or choose tags...',
            plugins: ['remove_button'],
        });
    }
</script>
@endsection