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
                        <div>
                            <label class="block text-xs font-bold text-navy uppercase tracking-wider">Article Content *</label>
                            <p class="text-[11px] text-gray-400">Professional TipTap WYSIWYG &amp; HTML Source Editor with real-time statistics</p>
                        </div>
                    </div>

                    <div class="tiptap-container bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden" data-content="{{ base64_encode(old('content', $post->content ?? '')) }}">
                        
                        <!-- HEADER BAR: Status, Word Count, Hierarchy Warning, Mode Toggles -->
                        <div class="px-4 py-2.5 bg-slate-900 text-white flex flex-wrap items-center justify-between gap-3 border-b border-slate-800">
                            <div class="flex items-center gap-2">
                                <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded text-[11px] font-bold bg-cyan/20 text-cyan">
                                    <span class="w-1.5 h-1.5 rounded-full bg-cyan animate-pulse"></span>
                                    TipTap Rich Text v3
                                </span>
                                <span class="heading-hierarchy-warning hidden text-xs font-semibold bg-amber-500/20 text-amber-300 border border-amber-500/30 px-2.5 py-0.5 rounded"></span>
                            </div>

                            <div class="flex items-center gap-3 text-xs">
                                <!-- Stats Counters -->
                                <div class="flex items-center gap-2 text-slate-400 font-mono text-[11px]">
                                    <span class="stat-words-count bg-slate-800 px-2 py-0.5 rounded border border-slate-700 text-slate-300">0 words</span>
                                    <span class="stat-chars-count bg-slate-800 px-2 py-0.5 rounded border border-slate-700 text-slate-300">0 chars</span>
                                    <span class="stat-reading-time bg-slate-800 px-2 py-0.5 rounded border border-slate-700 text-slate-300">1 min read</span>
                                </div>

                                <div class="h-4 w-px bg-slate-700"></div>

                                <!-- Live Preview Button -->
                                <button type="button" class="btn-live-preview inline-flex items-center gap-1 px-2.5 py-1 text-xs font-semibold rounded bg-slate-800 text-slate-200 hover:bg-slate-700 hover:text-white border border-slate-700 transition" title="Preview article as rendered on live site">
                                    <svg class="w-3.5 h-3.5 text-cyan" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    Preview
                                </button>

                                <!-- HTML / Source Mode Button -->
                                <button type="button" class="btn-source-mode inline-flex items-center gap-1 px-2.5 py-1 text-xs font-semibold rounded bg-slate-800 text-slate-200 hover:bg-slate-700 hover:text-white border border-slate-700 transition" title="Switch between Visual and HTML Source Code">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/></svg>
                                    HTML Source
                                </button>

                                <!-- Fullscreen Button -->
                                <button type="button" class="btn-fullscreen p-1 text-slate-400 hover:text-white rounded hover:bg-slate-800 transition" title="Toggle Fullscreen Focus Mode">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"/></svg>
                                </button>

                                <!-- Shortcuts Help Button -->
                                <button type="button" class="btn-shortcuts p-1 text-slate-400 hover:text-white rounded hover:bg-slate-800 transition" title="Keyboard Shortcuts">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                </button>
                            </div>
                        </div>

                        <!-- MAIN TOOLBAR -->
                        <div class="tiptap-toolbar p-2 bg-slate-50 border-b border-gray-200 flex flex-wrap items-center gap-1 text-xs">
                            
                            <!-- Undo / Redo -->
                            <div class="flex items-center gap-0.5 border-r border-gray-300 pr-1.5 mr-1">
                                <button type="button" class="btn-undo p-1.5 rounded text-gray-700 hover:bg-gray-200 hover:text-navy transition" title="Undo (Ctrl+Z)">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/></svg>
                                </button>
                                <button type="button" class="btn-redo p-1.5 rounded text-gray-700 hover:bg-gray-200 hover:text-navy transition" title="Redo (Ctrl+Y / Ctrl+Shift+Z)">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 10h-10a8 8 0 00-8 8v2m18-10l-6 6m6-6l-6-6"/></svg>
                                </button>
                            </div>

                            <!-- Block Type Selector -->
                            <div class="mr-1">
                                <select class="select-block-format text-xs font-semibold bg-white border border-gray-300 rounded px-2.5 py-1 text-gray-800 focus:ring-1 focus:ring-cyan focus:border-cyan">
                                    <option value="p">Paragraph</option>
                                    <option value="h1">Heading 1 (H1)</option>
                                    <option value="h2">Heading 2 (H2)</option>
                                    <option value="h3">Heading 3 (H3)</option>
                                    <option value="h4">Heading 4 (H4)</option>
                                    <option value="h5">Heading 5 (H5)</option>
                                    <option value="h6">Heading 6 (H6)</option>
                                </select>
                            </div>

                            <!-- Inline Formatting -->
                            <div class="flex items-center gap-0.5 border-r border-gray-300 pr-1.5 mr-1">
                                <button type="button" class="btn-bold w-7 h-7 flex items-center justify-center rounded font-extrabold text-sm text-gray-700 hover:bg-gray-200 transition" title="Bold (Ctrl+B)">B</button>
                                <button type="button" class="btn-italic w-7 h-7 flex items-center justify-center rounded italic font-serif text-sm text-gray-700 hover:bg-gray-200 transition" title="Italic (Ctrl+I)">I</button>
                                <button type="button" class="btn-underline w-7 h-7 flex items-center justify-center rounded underline text-sm text-gray-700 hover:bg-gray-200 transition" title="Underline (Ctrl+U)">U</button>
                                <button type="button" class="btn-strike w-7 h-7 flex items-center justify-center rounded line-through text-sm text-gray-700 hover:bg-gray-200 transition" title="Strikethrough">S</button>
                                <button type="button" class="btn-code w-7 h-7 flex items-center justify-center rounded font-mono text-xs text-gray-700 hover:bg-gray-200 transition" title="Inline Code">&lt;/&gt;</button>
                                <button type="button" class="btn-superscript w-7 h-7 flex items-center justify-center rounded text-xs text-gray-700 hover:bg-gray-200 transition" title="Superscript">X<sup>2</sup></button>
                                <button type="button" class="btn-subscript w-7 h-7 flex items-center justify-center rounded text-xs text-gray-700 hover:bg-gray-200 transition" title="Subscript">X<sub>2</sub></button>
                            </div>

                            <!-- Text Colors & Highlight Dropdowns -->
                            <div class="flex items-center gap-1 border-r border-gray-300 pr-1.5 mr-1" x-data="{ colorOpen: false, highlightOpen: false }">
                                <!-- Text Color Menu -->
                                <div class="relative">
                                    <button type="button" @click="colorOpen = !colorOpen; highlightOpen = false" class="p-1.5 rounded text-gray-700 hover:bg-gray-200 flex items-center gap-0.5 transition" title="Text Color">
                                        <span class="font-bold text-xs underline decoration-2 decoration-red-500">A</span>
                                        <svg class="w-3 h-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                    </button>
                                    <div x-show="colorOpen" @click.away="colorOpen = false" x-cloak class="absolute left-0 top-full mt-1 z-30 bg-white border border-gray-200 shadow-xl rounded-lg p-2 w-48 space-y-2">
                                        <div class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Text Color</div>
                                        <div class="grid grid-cols-6 gap-1.5">
                                            <button type="button" data-color="#0A1628" class="btn-color-preset w-6 h-6 rounded bg-[#0A1628] border border-gray-300" title="Navy"></button>
                                            <button type="button" data-color="#1E293B" class="btn-color-preset w-6 h-6 rounded bg-[#1E293B] border border-gray-300" title="Slate"></button>
                                            <button type="button" data-color="#00D4AA" class="btn-color-preset w-6 h-6 rounded bg-[#00D4AA] border border-gray-300" title="Cyan"></button>
                                            <button type="button" data-color="#FF6B35" class="btn-color-preset w-6 h-6 rounded bg-[#FF6B35] border border-gray-300" title="Orange"></button>
                                            <button type="button" data-color="#2563EB" class="btn-color-preset w-6 h-6 rounded bg-[#2563EB] border border-gray-300" title="Blue"></button>
                                            <button type="button" data-color="#DC2626" class="btn-color-preset w-6 h-6 rounded bg-[#DC2626] border border-gray-300" title="Red"></button>
                                            <button type="button" data-color="#16A34A" class="btn-color-preset w-6 h-6 rounded bg-[#16A34A] border border-gray-300" title="Green"></button>
                                            <button type="button" data-color="#9333EA" class="btn-color-preset w-6 h-6 rounded bg-[#9333EA] border border-gray-300" title="Purple"></button>
                                            <button type="button" data-color="#D97706" class="btn-color-preset w-6 h-6 rounded bg-[#D97706] border border-gray-300" title="Amber"></button>
                                            <button type="button" data-color="" class="btn-color-preset col-span-3 text-[10px] py-1 border border-gray-300 rounded font-semibold text-gray-600 hover:bg-gray-100">Reset</button>
                                        </div>
                                    </div>
                                </div>

                                <!-- Highlight Menu -->
                                <div class="relative">
                                    <button type="button" @click="highlightOpen = !highlightOpen; colorOpen = false" class="p-1.5 rounded text-gray-700 hover:bg-gray-200 flex items-center gap-0.5 transition" title="Highlight Color">
                                        <span class="px-1 bg-yellow-200 font-bold text-xs rounded-sm">H</span>
                                        <svg class="w-3 h-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                    </button>
                                    <div x-show="highlightOpen" @click.away="highlightOpen = false" x-cloak class="absolute left-0 top-full mt-1 z-30 bg-white border border-gray-200 shadow-xl rounded-lg p-2 w-48 space-y-2">
                                        <div class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Highlight Color</div>
                                        <div class="grid grid-cols-5 gap-1.5">
                                            <button type="button" data-color="#FEF08A" class="btn-highlight-preset w-6 h-6 rounded bg-[#FEF08A] border border-amber-300" title="Yellow"></button>
                                            <button type="button" data-color="#A7F3D0" class="btn-highlight-preset w-6 h-6 rounded bg-[#A7F3D0] border border-emerald-300" title="Emerald"></button>
                                            <button type="button" data-color="#BAE6FD" class="btn-highlight-preset w-6 h-6 rounded bg-[#BAE6FD] border border-sky-300" title="Sky"></button>
                                            <button type="button" data-color="#FBCFE8" class="btn-highlight-preset w-6 h-6 rounded bg-[#FBCFE8] border border-pink-300" title="Pink"></button>
                                            <button type="button" data-color="#FED7AA" class="btn-highlight-preset w-6 h-6 rounded bg-[#FED7AA] border border-orange-300" title="Orange"></button>
                                            <button type="button" data-color="" class="btn-highlight-preset col-span-5 text-[10px] py-1 border border-gray-300 rounded font-semibold text-gray-600 hover:bg-gray-100">Remove</button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Alignments -->
                            <div class="flex items-center gap-0.5 border-r border-gray-300 pr-1.5 mr-1">
                                <button type="button" class="btn-align-left p-1.5 rounded text-gray-700 hover:bg-gray-200 transition" title="Align Left">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h10M4 18h16"/></svg>
                                </button>
                                <button type="button" class="btn-align-center p-1.5 rounded text-gray-700 hover:bg-gray-200 transition" title="Align Center">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M7 12h10M4 18h16"/></svg>
                                </button>
                                <button type="button" class="btn-align-right p-1.5 rounded text-gray-700 hover:bg-gray-200 transition" title="Align Right">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M10 12h10M4 18h16"/></svg>
                                </button>
                                <button type="button" class="btn-align-justify p-1.5 rounded text-gray-700 hover:bg-gray-200 transition" title="Align Justify">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                                </button>
                            </div>

                            <!-- Lists, Quote, HR -->
                            <div class="flex items-center gap-0.5 border-r border-gray-300 pr-1.5 mr-1">
                                <button type="button" class="btn-bullet-list p-1.5 rounded text-gray-700 hover:bg-gray-200 transition" title="Bullet List">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16M2 6h.01M2 12h.01M2 18h.01"/></svg>
                                </button>
                                <button type="button" class="btn-ordered-list p-1.5 rounded text-gray-700 hover:bg-gray-200 transition" title="Numbered List">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 6h13M7 12h13M7 18h13M3 6h1v4M3 14h2v2H3v2h3"/></svg>
                                </button>
                                <button type="button" class="btn-blockquote p-1.5 rounded text-gray-700 hover:bg-gray-200 transition" title="Blockquote">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/></svg>
                                </button>
                                <button type="button" class="btn-hr p-1.5 rounded text-gray-700 hover:bg-gray-200 transition" title="Horizontal Rule">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 12h16"/></svg>
                                </button>
                            </div>

                            <!-- Links & Media -->
                            <div class="flex items-center gap-0.5 border-r border-gray-300 pr-1.5 mr-1">
                                <button type="button" class="btn-link p-1.5 rounded text-gray-700 hover:bg-gray-200 transition" title="Insert / Edit Link">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
                                </button>
                                <button type="button" class="btn-image p-1.5 rounded text-gray-700 hover:bg-gray-200 transition" title="Upload or Insert Image">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                </button>
                            </div>

                            <!-- Table Operations Dropdown -->
                            <div class="relative mr-1" x-data="{ tableOpen: false }">
                                <button type="button" @click="tableOpen = !tableOpen" class="px-2 py-1 rounded text-gray-700 bg-white border border-gray-300 hover:bg-gray-100 flex items-center gap-1 font-semibold transition" title="Table Manager">
                                    <svg class="w-3.5 h-3.5 text-navy" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                                    <span>Table</span>
                                    <svg class="w-3 h-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                </button>
                                <div x-show="tableOpen" @click.away="tableOpen = false" x-cloak class="absolute left-0 top-full mt-1 z-30 bg-white border border-gray-200 shadow-xl rounded-lg p-1.5 w-48 text-xs divide-y divide-gray-100">
                                    <div class="py-1">
                                        <button type="button" @click="tableOpen = false" class="btn-table-insert w-full text-left px-2.5 py-1.5 rounded hover:bg-cyan/10 hover:text-navy font-semibold flex items-center gap-2">
                                            <span>➕ Insert Table...</span>
                                        </button>
                                    </div>
                                    <div class="py-1 space-y-0.5">
                                        <button type="button" @click="tableOpen = false" class="btn-table-add-row-before w-full text-left px-2.5 py-1 rounded hover:bg-gray-100">Add Row Above</button>
                                        <button type="button" @click="tableOpen = false" class="btn-table-add-row-after w-full text-left px-2.5 py-1 rounded hover:bg-gray-100">Add Row Below</button>
                                        <button type="button" @click="tableOpen = false" class="btn-table-del-row w-full text-left px-2.5 py-1 rounded hover:bg-red-50 text-red-600 font-medium">Delete Row</button>
                                    </div>
                                    <div class="py-1 space-y-0.5">
                                        <button type="button" @click="tableOpen = false" class="btn-table-add-col-before w-full text-left px-2.5 py-1 rounded hover:bg-gray-100">Add Column Left</button>
                                        <button type="button" @click="tableOpen = false" class="btn-table-add-col-after w-full text-left px-2.5 py-1 rounded hover:bg-gray-100">Add Column Right</button>
                                        <button type="button" @click="tableOpen = false" class="btn-table-del-col w-full text-left px-2.5 py-1 rounded hover:bg-red-50 text-red-600 font-medium">Delete Column</button>
                                    </div>
                                    <div class="py-1 space-y-0.5">
                                        <button type="button" @click="tableOpen = false" class="btn-table-merge-cells w-full text-left px-2.5 py-1 rounded hover:bg-gray-100">Merge Cells</button>
                                        <button type="button" @click="tableOpen = false" class="btn-table-split-cell w-full text-left px-2.5 py-1 rounded hover:bg-gray-100">Split Cell</button>
                                        <button type="button" @click="tableOpen = false" class="btn-table-toggle-header w-full text-left px-2.5 py-1 rounded hover:bg-gray-100">Toggle Header Row</button>
                                    </div>
                                    <div class="pt-1">
                                        <button type="button" @click="tableOpen = false" class="btn-table-delete w-full text-left px-2.5 py-1.5 rounded hover:bg-red-600 hover:text-white text-red-600 font-bold">Delete Entire Table</button>
                                    </div>
                                </div>
                            </div>

                            <!-- Code Block with Language Selection -->
                            <div class="flex items-center gap-1 border-r border-gray-300 pr-1.5 mr-1">
                                <button type="button" class="btn-code-block px-2 py-1 rounded text-gray-700 bg-white border border-gray-300 hover:bg-gray-100 font-mono text-xs flex items-center gap-1 transition" title="Insert or Toggle Code Block">
                                    <span class="font-bold text-navy">{ }</span>
                                    <span>Code Block</span>
                                </button>
                                <select class="select-code-lang text-[11px] font-mono bg-white border border-gray-300 rounded px-1.5 py-1 text-gray-700 focus:ring-1 focus:ring-cyan focus:border-cyan" title="Code Block Language">
                                    <option value="javascript">JavaScript</option>
                                    <option value="json">JSON</option>
                                    <option value="python">Python</option>
                                    <option value="bash">Bash / Shell</option>
                                    <option value="html">HTML</option>
                                    <option value="css">CSS</option>
                                    <option value="sql">SQL</option>
                                    <option value="php">PHP</option>
                                    <option value="yaml">YAML</option>
                                    <option value="typescript">TypeScript</option>
                                </select>
                            </div>

                            <!-- Special Characters & Utilities -->
                            <div class="flex items-center gap-0.5 ml-auto">
                                <button type="button" class="btn-special-chars p-1.5 rounded text-gray-700 hover:bg-gray-200 transition" title="Insert Special Character (&copy;, &trade;, &rarr;)">Ω</button>
                                <button type="button" class="btn-find-replace p-1.5 rounded text-gray-700 hover:bg-gray-200 transition" title="Find and Replace in Post">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                                </button>
                                <button type="button" class="btn-clear-format p-1.5 rounded text-gray-700 hover:bg-gray-200 transition" title="Clear Formatting (Remove marks & nodes)">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                </button>
                            </div>
                        </div>

                        <!-- FIND & REPLACE BAR (Inline drawer) -->
                        <div class="tiptap-find-replace-bar hidden bg-slate-100 border-b border-gray-200 px-4 py-2 flex flex-wrap items-center gap-2 text-xs">
                            <span class="font-bold text-gray-600 text-[11px] uppercase">Find &amp; Replace:</span>
                            <input type="text" placeholder="Find text..." class="find-input text-xs border border-gray-300 rounded px-2 py-1 focus:ring-1 focus:ring-cyan focus:border-cyan w-40">
                            <input type="text" placeholder="Replace with..." class="replace-input text-xs border border-gray-300 rounded px-2 py-1 focus:ring-1 focus:ring-cyan focus:border-cyan w-40">
                            <button type="button" class="btn-do-replace-all px-2.5 py-1 bg-navy text-white font-bold rounded hover:bg-slate-800 transition">Replace All</button>
                            <button type="button" onclick="this.closest('.tiptap-find-replace-bar').classList.add('hidden')" class="text-gray-400 hover:text-gray-600 p-1">&times;</button>
                        </div>

                        <!-- VISUAL EDITOR ELEMENT -->
                        <div class="editor-element w-full bg-white min-h-[480px] focus:outline-none"></div>

                        <!-- HTML / SOURCE CODE EDITOR ELEMENT (Two-way sync) -->
                        <textarea class="source-editor-element hidden w-full min-h-[480px] p-4 font-mono text-xs text-slate-100 bg-slate-950 focus:outline-none border-none resize-y leading-relaxed" spellcheck="false"></textarea>

                        <!-- HIDDEN FORM INPUT FOR LARAVEL POST DATA -->
                        <input type="hidden" name="content" class="content-input" value="{{ old('content', $post->content ?? '') }}">

                        <!-- MODAL 1: INSERT / EDIT LINK -->
                        <div class="tiptap-link-modal hidden fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4">
                            <div class="bg-white rounded-xl shadow-2xl max-w-md w-full p-5 space-y-4">
                                <div class="flex items-center justify-between border-b border-gray-100 pb-2">
                                    <h3 class="text-sm font-bold text-navy flex items-center gap-2">
                                        <svg class="w-4 h-4 text-cyan" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
                                        Insert / Edit Hyperlink
                                    </h3>
                                    <button type="button" class="btn-link-cancel text-gray-400 hover:text-gray-600 text-lg">&times;</button>
                                </div>
                                <div class="space-y-3 text-xs">
                                    <div>
                                        <label class="block font-bold text-gray-700 uppercase mb-1">Target URL *</label>
                                        <input type="url" class="link-modal-url w-full text-xs border border-gray-300 rounded-lg p-2.5 focus:border-cyan focus:ring-cyan" placeholder="https://example.com/guide">
                                    </div>
                                    <div>
                                        <label class="block font-bold text-gray-700 uppercase mb-1">Display Text (optional if text selected)</label>
                                        <input type="text" class="link-modal-text w-full text-xs border border-gray-300 rounded-lg p-2.5 focus:border-cyan focus:ring-cyan" placeholder="e.g. Official Certification Blueprint">
                                    </div>
                                    <div class="flex items-center gap-2 pt-1">
                                        <input type="checkbox" id="link_target_blank_create" class="link-modal-target rounded border-gray-300 text-cyan focus:ring-cyan" checked>
                                        <label for="link_target_blank_create" class="text-xs text-gray-700 font-medium cursor-pointer">Open link in new browser tab (<code>target="_blank"</code>)</label>
                                    </div>
                                </div>
                                <div class="flex items-center justify-between pt-2 border-t border-gray-100">
                                    <button type="button" class="btn-link-unlink text-xs font-bold text-rose-600 hover:text-rose-800">Remove Link</button>
                                    <div class="flex gap-2">
                                        <button type="button" class="btn-link-cancel px-3 py-1.5 text-xs font-bold text-gray-600 hover:bg-gray-100 rounded-lg">Cancel</button>
                                        <button type="button" class="btn-link-save px-4 py-1.5 text-xs font-bold text-white bg-navy hover:bg-slate-800 rounded-lg shadow">Apply Link</button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- MODAL 2: INSERT / UPLOAD IMAGE WITH METADATA -->
                        <div class="tiptap-image-modal hidden fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4">
                            <div class="bg-white rounded-xl shadow-2xl max-w-lg w-full p-6 space-y-4">
                                <div class="flex items-center justify-between border-b border-gray-100 pb-2">
                                    <h3 class="text-sm font-bold text-navy flex items-center gap-2">
                                        <svg class="w-4 h-4 text-cyan" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                        Insert or Upload Inline Article Image
                                    </h3>
                                    <button type="button" class="btn-image-cancel text-gray-400 hover:text-gray-600 text-lg">&times;</button>
                                </div>

                                <div class="space-y-3 text-xs">
                                    <div>
                                        <label class="block font-bold text-gray-700 uppercase mb-1">Option A: Upload Image File</label>
                                        <input type="file" accept="image/*" class="image-modal-file w-full text-xs text-gray-600 file:mr-2 file:py-1.5 file:px-3 file:rounded-md file:border-0 file:text-xs file:font-bold file:bg-navy/10 file:text-navy hover:file:bg-navy/20">
                                    </div>

                                    <div class="relative flex items-center justify-center">
                                        <div class="border-t border-gray-200 w-full"></div>
                                        <span class="bg-white px-2 text-[10px] font-bold text-gray-400 uppercase tracking-widest absolute">OR</span>
                                    </div>

                                    <div>
                                        <label class="block font-bold text-gray-700 uppercase mb-1">Option B: Direct Image URL</label>
                                        <input type="url" class="image-modal-url w-full text-xs border border-gray-300 rounded-lg p-2.5 focus:border-cyan focus:ring-cyan" placeholder="https://example.com/diagram.png">
                                    </div>

                                    <div class="grid grid-cols-2 gap-3 pt-1">
                                        <div>
                                            <label class="block font-bold text-gray-700 uppercase mb-1">Alt Text (SEO &amp; Accessibility) *</label>
                                            <input type="text" class="image-modal-alt w-full text-xs border border-gray-300 rounded-lg p-2.5 focus:border-cyan focus:ring-cyan" placeholder="Descriptive visual content...">
                                        </div>
                                        <div>
                                            <label class="block font-bold text-gray-700 uppercase mb-1">Title / Caption</label>
                                            <input type="text" class="image-modal-title w-full text-xs border border-gray-300 rounded-lg p-2.5 focus:border-cyan focus:ring-cyan" placeholder="Tooltip or caption...">
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-2 gap-3 pt-1">
                                        <div>
                                            <label class="block font-bold text-gray-700 uppercase mb-1">Alignment</label>
                                            <select class="image-modal-align w-full text-xs border border-gray-300 rounded-lg p-2.5 focus:border-cyan focus:ring-cyan">
                                                <option value="center" selected>Center (Default)</option>
                                                <option value="left">Left-Aligned</option>
                                                <option value="right">Right-Aligned</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block font-bold text-gray-700 uppercase mb-1">Display Width</label>
                                            <select class="image-modal-width w-full text-xs border border-gray-300 rounded-lg p-2.5 focus:border-cyan focus:ring-cyan">
                                                <option value="100%" selected>100% (Full Width)</option>
                                                <option value="75%">75% Width</option>
                                                <option value="50%">50% Width</option>
                                                <option value="35%">35% Width</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="image-modal-progress hidden p-2 bg-cyan/10 rounded-lg border border-cyan/30 text-cyan text-xs font-semibold flex items-center gap-2">
                                        <svg class="animate-spin h-4 w-4 text-cyan" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                                        Uploading image securely to server...
                                    </div>
                                </div>

                                <div class="flex justify-end gap-2 pt-2 border-t border-gray-100">
                                    <button type="button" class="btn-image-cancel px-3 py-1.5 text-xs font-bold text-gray-600 hover:bg-gray-100 rounded-lg">Cancel</button>
                                    <button type="button" class="btn-image-save px-4 py-1.5 text-xs font-bold text-white bg-navy hover:bg-slate-800 rounded-lg shadow">Insert Image</button>
                                </div>
                            </div>
                        </div>

                        <!-- MODAL 3: SPECIAL CHARACTERS PICKER -->
                        <div class="tiptap-special-chars-modal hidden fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4">
                            <div class="bg-white rounded-xl shadow-2xl max-w-sm w-full p-5 space-y-4">
                                <div class="flex items-center justify-between border-b border-gray-100 pb-2">
                                    <h3 class="text-sm font-bold text-navy">Special Symbols &amp; Characters</h3>
                                    <button type="button" class="btn-special-chars-cancel text-gray-400 hover:text-gray-600 text-lg">&times;</button>
                                </div>
                                <div class="grid grid-cols-7 gap-1.5 text-center text-sm font-semibold">
                                    @foreach(['&copy;', '&reg;', '&trade;', '&sect;', '&para;', '&deg;', '&plusmn;', '&times;', '&divide;', '&ne;', '&le;', '&ge;', '&asymp;', '&infin;', '&rarr;', '&larr;', '&uarr;', '&darr;', '&harr;', '&radic;', '&sum;', '&euro;', '&pound;', '&yen;', '&#8377;', '&bull;', '&hellip;', '&ndash;', '&mdash;'] as $entity)
                                        <button type="button" data-char="{!! $entity !!}" class="btn-insert-char p-2 border border-gray-200 rounded hover:bg-cyan hover:text-white transition">{!! $entity !!}</button>
                                    @endforeach
                                </div>
                                <div class="flex justify-end pt-2 border-t border-gray-100">
                                    <button type="button" class="btn-special-chars-cancel px-3 py-1.5 text-xs font-bold text-gray-600 hover:bg-gray-100 rounded-lg">Close</button>
                                </div>
                            </div>
                        </div>

                        <!-- MODAL 4: KEYBOARD SHORTCUTS REFERENCE -->
                        <div class="tiptap-shortcuts-modal hidden fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4">
                            <div class="bg-white rounded-xl shadow-2xl max-w-md w-full p-5 space-y-4">
                                <div class="flex items-center justify-between border-b border-gray-100 pb-2">
                                    <h3 class="text-sm font-bold text-navy">Keyboard Shortcuts</h3>
                                    <button type="button" class="btn-shortcuts-close text-gray-400 hover:text-gray-600 text-lg">&times;</button>
                                </div>
                                <div class="space-y-2 text-xs divide-y divide-gray-100">
                                    <div class="flex justify-between py-1"><span class="text-gray-600">Bold</span><kbd class="font-mono bg-gray-100 px-2 py-0.5 rounded border border-gray-200">Ctrl + B</kbd></div>
                                    <div class="flex justify-between py-1"><span class="text-gray-600">Italic</span><kbd class="font-mono bg-gray-100 px-2 py-0.5 rounded border border-gray-200">Ctrl + I</kbd></div>
                                    <div class="flex justify-between py-1"><span class="text-gray-600">Underline</span><kbd class="font-mono bg-gray-100 px-2 py-0.5 rounded border border-gray-200">Ctrl + U</kbd></div>
                                    <div class="flex justify-between py-1"><span class="text-gray-600">Heading 1 - 3</span><kbd class="font-mono bg-gray-100 px-2 py-0.5 rounded border border-gray-200">Ctrl + Alt + 1..3</kbd></div>
                                    <div class="flex justify-between py-1"><span class="text-gray-600">Bullet List</span><kbd class="font-mono bg-gray-100 px-2 py-0.5 rounded border border-gray-200">Ctrl + Shift + 8</kbd></div>
                                    <div class="flex justify-between py-1"><span class="text-gray-600">Numbered List</span><kbd class="font-mono bg-gray-100 px-2 py-0.5 rounded border border-gray-200">Ctrl + Shift + 7</kbd></div>
                                    <div class="flex justify-between py-1"><span class="text-gray-600">Code Block</span><kbd class="font-mono bg-gray-100 px-2 py-0.5 rounded border border-gray-200">Ctrl + Alt + C</kbd></div>
                                    <div class="flex justify-between py-1"><span class="text-gray-600">Undo / Redo</span><kbd class="font-mono bg-gray-100 px-2 py-0.5 rounded border border-gray-200">Ctrl + Z / Ctrl + Y</kbd></div>
                                </div>
                                <div class="flex justify-end pt-2 border-t border-gray-100">
                                    <button type="button" class="btn-shortcuts-close px-3 py-1.5 text-xs font-bold text-gray-600 hover:bg-gray-100 rounded-lg">Close</button>
                                </div>
                            </div>
                        </div>

                        <!-- MODAL 5: FULL ARTICLE LIVE PREVIEW MODAL -->
                        <div class="tiptap-preview-modal hidden fixed inset-0 z-50 flex items-center justify-center bg-black/70 p-4">
                            <div class="bg-white rounded-2xl shadow-2xl max-w-4xl w-full max-h-[90vh] flex flex-col overflow-hidden">
                                <div class="px-6 py-4 bg-slate-900 text-white flex items-center justify-between border-b border-slate-800">
                                    <div class="flex items-center gap-2">
                                        <span class="w-2.5 h-2.5 rounded-full bg-cyan"></span>
                                        <h3 class="text-sm font-bold tracking-tight">Public Article Live Preview</h3>
                                        <span class="text-[11px] text-slate-400 font-normal">(As candidates and search engines will see it)</span>
                                    </div>
                                    <button type="button" class="btn-preview-close text-slate-400 hover:text-white text-xl">&times;</button>
                                </div>
                                <div class="p-8 overflow-y-auto flex-1 bg-white">
                                    <div class="tiptap-preview-content prose prose-lg prose-cyan max-w-none text-gray-700"></div>
                                </div>
                                <div class="px-6 py-3 bg-gray-50 border-t border-gray-200 flex justify-end">
                                    <button type="button" class="btn-preview-close px-4 py-1.5 text-xs font-bold text-gray-600 hover:bg-gray-200 rounded-lg">Close Preview</button>
                                </div>
                            </div>
                        </div>

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

@section('styles')
<style>
    /* Fullscreen editor styling */
    .tiptap-container.tiptap-fullscreen-active {
        position: fixed !important;
        inset: 0 !important;
        z-index: 9999 !important;
        width: 100vw !important;
        height: 100vh !important;
        border-radius: 0 !important;
        background: white !important;
        display: flex !important;
        flex-direction: column !important;
    }
    .tiptap-container.tiptap-fullscreen-active .editor-element,
    .tiptap-container.tiptap-fullscreen-active .source-editor-element {
        flex: 1 1 auto !important;
        overflow-y: auto !important;
        min-height: 0 !important;
        max-height: none !important;
    }
    /* Prose styling inside editor */
    .tiptap-container .ProseMirror {
        outline: none;
        min-height: 480px;
        padding: 1.5rem;
    }
    .tiptap-container .ProseMirror table {
        border-collapse: collapse;
        width: 100%;
        margin: 1.5rem 0;
        table-layout: fixed;
    }
    .tiptap-container .ProseMirror th,
    .tiptap-container .ProseMirror td {
        border: 1px solid #cbd5e1;
        padding: 0.75rem 1rem;
        vertical-align: top;
        position: relative;
    }
    .tiptap-container .ProseMirror th {
        background-color: #0A1628;
        color: white;
        font-weight: 700;
    }
    .tiptap-container .ProseMirror img {
        max-width: 100%;
        height: auto;
        border-radius: 0.5rem;
    }
    .tiptap-container .ProseMirror .tiptap-image-wrap {
        margin: 1.5rem 0;
    }
    .tiptap-container .ProseMirror .tiptap-image-wrap.align-center {
        display: flex;
        justify-content: center;
    }
    .tiptap-container .ProseMirror .tiptap-image-wrap.align-left {
        display: flex;
        justify-content: flex-start;
    }
    .tiptap-container .ProseMirror .tiptap-image-wrap.align-right {
        display: flex;
        justify-content: flex-end;
    }
    .tiptap-container .ProseMirror pre {
        background-color: #0A1628;
        color: #f8fafc;
        border-radius: 0.75rem;
        padding: 1.25rem;
        font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
        font-size: 0.875rem;
        margin: 1.5rem 0;
        overflow-x: auto;
    }
</style>
@endsection

@section('scripts')
@vite(['resources/js/blog-editor.js'])
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