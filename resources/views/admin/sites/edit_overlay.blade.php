@extends('layouts.admin')

@section('title', 'Edit Overlay: ' . $exam->exam_code . ' - ' . $site->name)

@section('styles')
@include('admin.partials.tiptap-styles')
@endsection

@section('content')
<div class="space-y-6" x-data="overlayWorkspace({
    faqs: {{ json_encode(old('faqs', $overlay->custom_faqs ?? [])) }},
    topics: {{ json_encode(old('custom_topics', $overlay->custom_topics ?? [])) }},
    customH1: {{ json_encode(old('custom_h1', $overlay->custom_h1 ?? '')) }},
    customHeaderTitle: {{ json_encode(old('custom_header_title', $overlay->custom_header_title ?? '')) }},
    metaTitle: {{ json_encode(old('meta_title', $overlay->meta_title ?? '')) }},
    metaDescription: {{ json_encode(old('meta_description', $overlay->meta_description ?? '')) }},
    metaKeywords: {{ json_encode(old('meta_keywords', $overlay->meta_keywords ?? '')) }},
    priceBundle: {{ json_encode(old('custom_price_bundle', $overlay->custom_price_bundle ?? '')) }},
    priceEngine: {{ json_encode(old('custom_price_engine', $overlay->custom_price_engine ?? '')) }},
    pricePdf: {{ json_encode(old('custom_price_pdf', $overlay->custom_price_pdf ?? '')) }},
    isActive: {{ json_encode(old('is_active', $overlay->exists ? (bool)$overlay->is_active : true)) }},
    isIndexed: {{ json_encode(old('is_indexed', $overlay->exists ? (bool)$overlay->is_indexed : true)) }}
})">

    <!-- Header & Navigation -->
    <div class="sm:flex sm:items-center sm:justify-between bg-white border border-gray-200 rounded-2xl p-6 shadow-sm">
        <div>
            <div class="flex items-center space-x-2 text-xs text-gray-400 font-semibold mb-1">
                <a href="{{ route('admin.sites.exams', $site->id) }}" class="text-cyan hover:underline">Site Catalog &amp; Overlays</a>
                <span>/</span>
                <span class="text-navy font-bold">{{ $exam->exam_code }}</span>
            </div>
            <div class="flex items-center gap-3">
                <h1 class="text-2xl font-black text-navy">Edit Overlay: {{ $exam->exam_code }} - {{ $site->name }}</h1>
                <span class="px-2.5 py-0.5 rounded-full text-xs font-bold {{ $overlay->is_active ? 'bg-emerald-100 text-emerald-800' : 'bg-gray-100 text-gray-700' }}">
                    {{ $overlay->is_active ? 'Visible on Site B' : 'Draft / Hidden' }}
                </span>
            </div>
            <p class="mt-1 text-sm text-gray-500">
                Customizing <strong class="text-navy">{{ $exam->exam_name }}</strong> ({{ $exam->vendor->name ?? 'Vendor' }}) for <strong class="text-navy">{{ $site->host }}</strong>.
                <span class="text-xs text-emerald-600 font-bold ml-1">&#10003; Shared core question bank remains intact.</span>
            </p>
        </div>
        <div class="mt-4 sm:mt-0 flex items-center gap-3">
            <a href="{{ $exam->url }}" target="_blank" class="px-4 py-2 border border-gray-300 rounded-xl text-xs font-bold text-gray-700 hover:bg-gray-50 transition flex items-center gap-1.5">
                <span>View Live Exam</span>
                <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
            </a>
            <a href="{{ route('admin.sites.exams', $site->id) }}" class="px-4 py-2 bg-slate-900 text-white rounded-xl text-xs font-bold hover:bg-slate-800 transition">
                &larr; Back to Catalog
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl text-sm font-semibold">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="p-4 bg-red-50 border border-red-200 text-red-800 rounded-xl text-sm font-semibold">
            {{ session('error') }}
        </div>
    @endif

    <!-- Main Overlay Form -->
    <form action="{{ route('admin.sites.exams.overlay.update', ['site' => $site->id, 'exam' => $exam->id]) }}" method="POST" class="space-y-6">
        @csrf

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            <!-- Left & Middle: Content, SEO, FAQs (2 Cols) -->
            <div class="lg:col-span-2 space-y-6">

                <!-- 01: HEADINGS & BRANDING TITLE -->
                <div class="bg-white border border-gray-200 rounded-2xl p-6 shadow-sm space-y-4">
                    <div class="border-b border-gray-100 pb-3">
                        <h3 class="text-sm font-bold text-navy uppercase tracking-wide">01. Headings &amp; Titles</h3>
                        <p class="text-xs text-gray-500">Provide site-specific headlines tailored to {{ $site->name }} audience.</p>
                    </div>

                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase mb-1">
                                Custom H1 Headline
                            </label>
                            <input type="text" name="custom_h1" value="{{ old('custom_h1', $overlay->custom_h1) }}"
                                   placeholder="e.g. {{ $exam->vendor->name ?? 'AWS' }} {{ $exam->exam_code }} Exam Questions &amp; Practice Tests"
                                   class="w-full rounded-xl border-gray-300 shadow-sm focus:border-cyan focus:ring-cyan text-sm">
                            <p class="text-[11px] text-gray-400 mt-1">Leave empty to fall back to Custom Header Title or Master Exam Title.</p>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase mb-1">
                                Custom Subheader / Header Title
                            </label>
                            <input type="text" name="custom_header_title" value="{{ old('custom_header_title', $overlay->custom_header_title) }}"
                                   placeholder="e.g. Pass {{ $exam->exam_code }} on Your First Try with ProExamTopics"
                                   class="w-full rounded-xl border-gray-300 shadow-sm focus:border-cyan focus:ring-cyan text-sm">
                            <p class="text-[11px] text-gray-400 mt-1">Displayed in hero badge and secondary headers.</p>
                        </div>
                    </div>
                </div>

                <!-- 02: EDITORIAL ARTICLE CONTENT (TIPTAP WYSIWYG) -->
                <div class="bg-white border border-gray-200 rounded-2xl p-6 shadow-sm space-y-4">
                    <div class="border-b border-gray-100 pb-3 flex items-center justify-between">
                        <div>
                            <h3 class="text-sm font-bold text-navy uppercase tracking-wide">02. Study Guide Article Content</h3>
                            <p class="text-xs text-gray-500">Publish unique editorial content for {{ $site->name }} to prevent duplicate-content penalties.</p>
                        </div>
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-cyan/10 text-cyan-800 border border-cyan/20">TipTap WYSIWYG</span>
                    </div>

                    <div class="space-y-4">
                        @include('admin.partials.tiptap-editor', [
                            'name' => 'custom_article_content',
                            'value' => old('custom_article_content', $overlay->custom_article_content ?? ''),
                            'placeholder' => 'Write unique, high-ranking study guide notes and syllabus coverage for ' . $exam->exam_code . ' on ' . $site->name . '...',
                            'minHeight' => '420px'
                        ])
                        @error('custom_article_content')
                            <p class="text-red-500 text-xs font-medium">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- 03: STRUCTURED FREQUENTLY ASKED QUESTIONS (FAQS) -->
                <div class="bg-white border border-gray-200 rounded-2xl p-6 shadow-sm space-y-4">
                    <div class="border-b border-gray-100 pb-3 flex items-center justify-between">
                        <div>
                            <h3 class="text-sm font-bold text-navy uppercase tracking-wide">03. Frequently Asked Questions (FAQs)</h3>
                            <p class="text-xs text-gray-500">Custom questions and answers for {{ $site->name }}. Powers the interactive accordion and Google FAQPage schema.</p>
                        </div>
                        <span class="text-xs font-mono font-bold text-gray-400" x-text="faqs.length + ' FAQs'"></span>
                    </div>

                    <div class="space-y-4">
                        <template x-for="(faq, index) in faqs" :key="index">
                            <div class="p-4 border border-gray-200 rounded-xl bg-gray-50/60 space-y-3 relative group">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-2">
                                        <span class="w-6 h-6 rounded-full bg-cyan/20 text-cyan text-xs font-black flex items-center justify-center" x-text="index + 1"></span>
                                        <span class="text-xs font-bold uppercase text-navy">Question &amp; Answer</span>
                                    </div>
                                    <button type="button" @click="removeFaq(index)" class="text-xs text-red-500 hover:text-red-700 font-bold flex items-center gap-1 transition">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                        <span>Remove</span>
                                    </button>
                                </div>

                                <div>
                                    <label class="block text-[11px] font-bold text-gray-700 uppercase mb-1">
                                        Question <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" :name="'faqs[' + index + '][question]'" x-model="faq.question"
                                           placeholder="e.g. How up to date are the {{ $exam->exam_code }} practice tests on {{ $site->name }}?"
                                           class="w-full border-gray-300 rounded-lg text-sm px-3 py-2 text-navy focus:border-cyan focus:ring-cyan shadow-sm bg-white">
                                </div>

                                <div>
                                    <label class="block text-[11px] font-bold text-gray-700 uppercase mb-1">
                                        Answer <span class="text-red-500">*</span>
                                    </label>
                                    <textarea :name="'faqs[' + index + '][answer]'" x-model="faq.answer" rows="3"
                                              placeholder="Detailed answer for candidates..."
                                              class="w-full border-gray-300 rounded-lg text-sm px-3 py-2 text-gray-700 focus:border-cyan focus:ring-cyan shadow-sm bg-white"></textarea>
                                </div>
                            </div>
                        </template>

                        <div x-show="faqs.length === 0" class="text-center py-8 border-2 border-dashed border-gray-200 rounded-xl bg-gray-50/40">
                            <svg class="w-8 h-8 text-gray-300 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            <p class="text-xs font-medium text-gray-500">No custom FAQs defined yet for this site.</p>
                            <p class="text-[11px] text-gray-400 mt-0.5">Click below to add unique FAQs for {{ $site->name }}. If empty, master catalog FAQs will be displayed.</p>
                        </div>

                        <div>
                            <button type="button" @click="addFaq()" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-navy text-white text-xs font-bold hover:bg-slate-800 transition shadow-sm">
                                <svg class="w-3.5 h-3.5 text-cyan" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                                <span>+ Add FAQ Item</span>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- 04: EXAM TOPICS & SYLLABUS DOMAINS -->
                <div class="bg-white border border-gray-200 rounded-2xl p-6 shadow-sm space-y-4">
                    <div class="border-b border-gray-100 pb-3 flex items-center justify-between">
                        <div>
                            <h3 class="text-sm font-bold text-navy uppercase tracking-wide">04. Exam Topics &amp; Syllabus Domains</h3>
                            <p class="text-xs text-gray-500">Custom syllabus objectives and percentage weightings for the "Exam Topics" tab on {{ $site->name }}.</p>
                        </div>
                        <span class="text-xs font-mono font-bold text-gray-400" x-text="topics.length + ' Domains'"></span>
                    </div>

                    <div class="space-y-4">
                        <template x-for="(topic, index) in topics" :key="index">
                            <div class="p-4 border border-gray-200 rounded-xl bg-gray-50/60 space-y-3 relative group">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-2">
                                        <span class="w-6 h-6 rounded-full bg-blue-100 text-[#2563eb] text-xs font-black flex items-center justify-center" x-text="index + 1"></span>
                                        <span class="text-xs font-bold uppercase text-navy" x-text="'Domain ' + (index + 1)"></span>
                                    </div>
                                    <button type="button" @click="removeTopic(index)" class="text-xs text-red-500 hover:text-red-700 font-bold flex items-center gap-1 transition">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                        <span>Remove</span>
                                    </button>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                    <div class="sm:col-span-2">
                                        <label class="block text-[11px] font-bold text-gray-700 uppercase mb-1">
                                            Domain / Topic Title <span class="text-red-500">*</span>
                                        </label>
                                        <input type="text" :name="'custom_topics[' + index + '][name]'" x-model="topic.name"
                                               placeholder="e.g. Domain 1: Core Architecture & Cloud Concepts"
                                               class="w-full border-gray-300 rounded-lg text-sm px-3 py-2 text-navy focus:border-cyan focus:ring-cyan shadow-sm bg-white font-medium">
                                    </div>

                                    <div>
                                        <label class="block text-[11px] font-bold text-gray-700 uppercase mb-1">
                                            Weight / Percentage
                                        </label>
                                        <input type="text" :name="'custom_topics[' + index + '][percentage]'" x-model="topic.percentage"
                                               placeholder="e.g. 25-30%"
                                               class="w-full border-gray-300 rounded-lg text-sm px-3 py-2 text-[#2563eb] focus:border-cyan focus:ring-cyan shadow-sm bg-white font-mono font-bold text-center">
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-[11px] font-bold text-gray-700 uppercase mb-1">
                                        Syllabus Summary / Objectives
                                    </label>
                                    <textarea :name="'custom_topics[' + index + '][description]'" x-model="topic.description" rows="2"
                                              placeholder="e.g. Fundamental benefits, high availability, scalability and agility..."
                                              class="w-full border-gray-300 rounded-lg text-sm px-3 py-2 text-gray-700 focus:border-cyan focus:ring-cyan shadow-sm bg-white"></textarea>
                                </div>
                            </div>
                        </template>

                        <div x-show="topics.length === 0" class="text-center py-8 border-2 border-dashed border-gray-200 rounded-xl bg-gray-50/40">
                            <svg class="w-8 h-8 text-gray-300 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                            <p class="text-xs font-medium text-gray-500">No custom topics defined yet for this site.</p>
                            <p class="text-[11px] text-gray-400 mt-0.5">Click below to add unique exam syllabus topics or load standard domains.</p>
                        </div>

                        <div class="flex flex-wrap items-center gap-2">
                            <button type="button" @click="addTopic()" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-navy text-white text-xs font-bold hover:bg-slate-800 transition shadow-sm">
                                <svg class="w-3.5 h-3.5 text-cyan" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                                <span>+ Add Exam Topic</span>
                            </button>
                            <button type="button" @click="loadSampleTopics()" x-show="topics.length === 0" class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl border border-gray-300 text-gray-700 text-xs font-bold hover:bg-gray-100 transition shadow-sm">
                                <svg class="w-3.5 h-3.5 text-[#2563eb]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" /></svg>
                                <span>Load Sample Template (3 Domains)</span>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- 05: SEO METADATA -->
                <div class="bg-white border border-gray-200 rounded-2xl p-6 shadow-sm space-y-4">
                    <div class="border-b border-gray-100 pb-3">
                        <h3 class="text-sm font-bold text-navy uppercase tracking-wide">05. Technical SEO Overrides</h3>
                        <p class="text-xs text-gray-500">Custom meta tags specifically for {{ $site->name }} search ranking.</p>
                    </div>

                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Custom Meta Title</label>
                            <input type="text" name="meta_title" value="{{ old('meta_title', $overlay->meta_title) }}"
                                   placeholder="Leave empty to use resolved template: {Code} Exam Dumps & Study Guide | {{ $site->name }}"
                                   class="w-full rounded-xl border-gray-300 shadow-sm focus:border-cyan focus:ring-cyan text-sm">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Custom Meta Description</label>
                            <textarea name="meta_description" rows="2"
                                      placeholder="Leave empty to use resolved default description..."
                                      class="w-full rounded-xl border-gray-300 shadow-sm focus:border-cyan focus:ring-cyan text-sm">{{ old('meta_description', $overlay->meta_description) }}</textarea>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Meta Keywords</label>
                            <input type="text" name="meta_keywords" value="{{ old('meta_keywords', $overlay->meta_keywords) }}"
                                   placeholder="e.g. {{ $exam->exam_code }}, proexamtopics {{ $exam->exam_code }}, pass {{ $exam->exam_code }} dumps"
                                   class="w-full rounded-xl border-gray-300 shadow-sm focus:border-cyan focus:ring-cyan text-sm">
                        </div>
                    </div>
                </div>

            </div>

            <!-- Right Column: Pricing, Catalog State, Save Action (1 Col) -->
            <div class="space-y-6">

                <!-- 05: CATALOG STATUS & VISIBILITY -->
                <div class="bg-white border border-gray-200 rounded-2xl p-6 shadow-sm space-y-4">
                    <h3 class="text-sm font-bold text-navy uppercase tracking-wide border-b border-gray-100 pb-3">Catalog State</h3>

                    <div class="space-y-4">
                        <label class="flex items-start space-x-3 cursor-pointer">
                            <input type="checkbox" name="is_active" value="1" {{ old('is_active', $overlay->exists ? $overlay->is_active : true) ? 'checked' : '' }}
                                   class="h-4 w-4 mt-0.5 text-cyan focus:ring-cyan rounded">
                            <div>
                                <span class="text-xs font-bold text-gray-800">Visible on {{ $site->name }}</span>
                                <p class="text-[11px] text-gray-400">If unchecked, this exam returns 404 on {{ $site->host }}.</p>
                            </div>
                        </label>

                        <label class="flex items-start space-x-3 cursor-pointer">
                            <input type="checkbox" name="is_indexed" value="1" {{ old('is_indexed', $overlay->exists ? $overlay->is_indexed : true) ? 'checked' : '' }}
                                   class="h-4 w-4 mt-0.5 text-cyan focus:ring-cyan rounded">
                            <div>
                                <span class="text-xs font-bold text-gray-800">Include in Sitemap &amp; Robots</span>
                                <p class="text-[11px] text-gray-400">Allows search engine crawlers to discover this page on {{ $site->host }}.</p>
                            </div>
                        </label>
                    </div>
                </div>

                <!-- 06: SITE-SPECIFIC PRICING -->
                <div class="bg-white border border-gray-200 rounded-2xl p-6 shadow-sm space-y-5">
                    <div class="border-b border-gray-100 pb-3">
                        <h3 class="text-sm font-bold text-navy uppercase tracking-wide">Custom Pricing &amp; Updates</h3>
                        <p class="text-xs text-gray-500">Override base and update period add-on prices charged on {{ $site->name }}. Leave blank to use core catalog defaults.</p>
                    </div>

                    <div class="space-y-5 text-xs">
                        <!-- 1. PDF + Test Engine Bundle Card -->
                        <div class="p-3.5 bg-gray-50/70 border border-gray-200/80 rounded-xl space-y-3">
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-gray-800 uppercase text-[11px] flex items-center gap-1.5">
                                    <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                                    PDF + Test Engine Bundle
                                </span>
                                <span class="text-[10px] text-gray-400 font-mono">Catalog: ${{ number_format($exam->effective_bundle_price, 2) }}</span>
                            </div>

                            <div>
                                <label class="block font-semibold text-gray-600 text-[11px] mb-1">Base Price ($)</label>
                                <input type="number" step="0.01" name="custom_price_bundle" value="{{ old('custom_price_bundle', $overlay->custom_price_bundle) }}"
                                       placeholder="Default: ${{ number_format($exam->effective_bundle_price, 2) }}"
                                       class="w-full rounded-lg border-gray-300 shadow-sm focus:border-cyan focus:ring-cyan text-xs font-mono py-1.5 px-2.5">
                            </div>

                            <div>
                                <label class="block font-semibold text-gray-600 text-[11px] mb-1">Update Period Add-ons ($)</label>
                                <div class="grid grid-cols-3 gap-2">
                                    <div>
                                        <span class="block text-[10px] text-gray-500 font-bold mb-0.5">3 Months</span>
                                        <input type="number" step="0.01" min="0" name="custom_update_price_bundle_3" value="{{ old('custom_update_price_bundle_3', $overlay->custom_update_price_bundle_3) }}"
                                               placeholder="${{ number_format($exam->update_price_3_months ?? 0, 2) }}"
                                               class="w-full rounded-lg border-gray-300 shadow-sm focus:border-cyan focus:ring-cyan text-[11px] font-mono py-1.5 px-2 text-center">
                                    </div>
                                    <div>
                                        <span class="block text-[10px] text-gray-500 font-bold mb-0.5">6 Months</span>
                                        <input type="number" step="0.01" min="0" name="custom_update_price_bundle_6" value="{{ old('custom_update_price_bundle_6', $overlay->custom_update_price_bundle_6) }}"
                                               placeholder="${{ number_format($exam->update_price_6_months ?? 10, 2) }}"
                                               class="w-full rounded-lg border-gray-300 shadow-sm focus:border-cyan focus:ring-cyan text-[11px] font-mono py-1.5 px-2 text-center">
                                    </div>
                                    <div>
                                        <span class="block text-[10px] text-gray-500 font-bold mb-0.5">12 Months</span>
                                        <input type="number" step="0.01" min="0" name="custom_update_price_bundle_12" value="{{ old('custom_update_price_bundle_12', $overlay->custom_update_price_bundle_12) }}"
                                               placeholder="${{ number_format($exam->update_price_12_months ?? 20, 2) }}"
                                               class="w-full rounded-lg border-gray-300 shadow-sm focus:border-cyan focus:ring-cyan text-[11px] font-mono py-1.5 px-2 text-center">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- 2. PDF Study Guide Card -->
                        <div class="p-3.5 bg-gray-50/70 border border-gray-200/80 rounded-xl space-y-3">
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-gray-800 uppercase text-[11px] flex items-center gap-1.5">
                                    <span class="w-2 h-2 rounded-full bg-cyan"></span>
                                    PDF Study Guide
                                </span>
                                <span class="text-[10px] text-gray-400 font-mono">Catalog: ${{ number_format($exam->price_pdf ?? 29.99, 2) }}</span>
                            </div>

                            <div>
                                <label class="block font-semibold text-gray-600 text-[11px] mb-1">Base Price ($)</label>
                                <input type="number" step="0.01" name="custom_price_pdf" value="{{ old('custom_price_pdf', $overlay->custom_price_pdf) }}"
                                       placeholder="Default: ${{ number_format($exam->price_pdf ?? 29.99, 2) }}"
                                       class="w-full rounded-lg border-gray-300 shadow-sm focus:border-cyan focus:ring-cyan text-xs font-mono py-1.5 px-2.5">
                            </div>

                            <div>
                                <label class="block font-semibold text-gray-600 text-[11px] mb-1">Update Period Add-ons ($)</label>
                                <div class="grid grid-cols-3 gap-2">
                                    <div>
                                        <span class="block text-[10px] text-gray-500 font-bold mb-0.5">3 Months</span>
                                        <input type="number" step="0.01" min="0" name="custom_update_price_pdf_3" value="{{ old('custom_update_price_pdf_3', $overlay->custom_update_price_pdf_3) }}"
                                               placeholder="${{ number_format($exam->update_price_3_months ?? 0, 2) }}"
                                               class="w-full rounded-lg border-gray-300 shadow-sm focus:border-cyan focus:ring-cyan text-[11px] font-mono py-1.5 px-2 text-center">
                                    </div>
                                    <div>
                                        <span class="block text-[10px] text-gray-500 font-bold mb-0.5">6 Months</span>
                                        <input type="number" step="0.01" min="0" name="custom_update_price_pdf_6" value="{{ old('custom_update_price_pdf_6', $overlay->custom_update_price_pdf_6) }}"
                                               placeholder="${{ number_format($exam->update_price_6_months ?? 10, 2) }}"
                                               class="w-full rounded-lg border-gray-300 shadow-sm focus:border-cyan focus:ring-cyan text-[11px] font-mono py-1.5 px-2 text-center">
                                    </div>
                                    <div>
                                        <span class="block text-[10px] text-gray-500 font-bold mb-0.5">12 Months</span>
                                        <input type="number" step="0.01" min="0" name="custom_update_price_pdf_12" value="{{ old('custom_update_price_pdf_12', $overlay->custom_update_price_pdf_12) }}"
                                               placeholder="${{ number_format($exam->update_price_12_months ?? 20, 2) }}"
                                               class="w-full rounded-lg border-gray-300 shadow-sm focus:border-cyan focus:ring-cyan text-[11px] font-mono py-1.5 px-2 text-center">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- 3. Test Engine Simulator Card -->
                        <div class="p-3.5 bg-gray-50/70 border border-gray-200/80 rounded-xl space-y-3">
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-gray-800 uppercase text-[11px] flex items-center gap-1.5">
                                    <span class="w-2 h-2 rounded-full bg-blue-600"></span>
                                    Test Engine Simulator
                                </span>
                                <span class="text-[10px] text-gray-400 font-mono">Catalog: ${{ number_format($exam->price_engine ?? 39.99, 2) }}</span>
                            </div>

                            <div>
                                <label class="block font-semibold text-gray-600 text-[11px] mb-1">Base Price ($)</label>
                                <input type="number" step="0.01" name="custom_price_engine" value="{{ old('custom_price_engine', $overlay->custom_price_engine) }}"
                                       placeholder="Default: ${{ number_format($exam->price_engine ?? 39.99, 2) }}"
                                       class="w-full rounded-lg border-gray-300 shadow-sm focus:border-cyan focus:ring-cyan text-xs font-mono py-1.5 px-2.5">
                            </div>

                            <div>
                                <label class="block font-semibold text-gray-600 text-[11px] mb-1">Update Period Add-ons ($)</label>
                                <div class="grid grid-cols-3 gap-2">
                                    <div>
                                        <span class="block text-[10px] text-gray-500 font-bold mb-0.5">3 Months</span>
                                        <input type="number" step="0.01" min="0" name="custom_update_price_engine_3" value="{{ old('custom_update_price_engine_3', $overlay->custom_update_price_engine_3) }}"
                                               placeholder="${{ number_format($exam->update_price_3_months ?? 0, 2) }}"
                                               class="w-full rounded-lg border-gray-300 shadow-sm focus:border-cyan focus:ring-cyan text-[11px] font-mono py-1.5 px-2 text-center">
                                    </div>
                                    <div>
                                        <span class="block text-[10px] text-gray-500 font-bold mb-0.5">6 Months</span>
                                        <input type="number" step="0.01" min="0" name="custom_update_price_engine_6" value="{{ old('custom_update_price_engine_6', $overlay->custom_update_price_engine_6) }}"
                                               placeholder="${{ number_format($exam->update_price_6_months ?? 10, 2) }}"
                                               class="w-full rounded-lg border-gray-300 shadow-sm focus:border-cyan focus:ring-cyan text-[11px] font-mono py-1.5 px-2 text-center">
                                    </div>
                                    <div>
                                        <span class="block text-[10px] text-gray-500 font-bold mb-0.5">12 Months</span>
                                        <input type="number" step="0.01" min="0" name="custom_update_price_engine_12" value="{{ old('custom_update_price_engine_12', $overlay->custom_update_price_engine_12) }}"
                                               placeholder="${{ number_format($exam->update_price_12_months ?? 20, 2) }}"
                                               class="w-full rounded-lg border-gray-300 shadow-sm focus:border-cyan focus:ring-cyan text-[11px] font-mono py-1.5 px-2 text-center">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- SAVE ACTIONS BOX -->
                <div class="bg-white border border-gray-200 rounded-2xl p-6 shadow-sm space-y-4">
                    <button type="submit" class="w-full py-3 px-4 bg-cyan hover:bg-cyan/90 text-white font-bold rounded-xl text-xs uppercase tracking-wider transition shadow-sm flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span>Save {{ $site->name }} Overlay</span>
                    </button>
                    <a href="{{ route('admin.sites.exams', $site->id) }}" class="block text-center text-xs font-bold text-gray-500 hover:text-navy">
                        Cancel &amp; Return
                    </a>
                </div>

            </div>

        </div>
    </form>

</div>
@endsection

@section('scripts')
@vite(['resources/js/blog-editor.js'])
<script>
function overlayWorkspace(initial) {
    return {
        faqs: Array.isArray(initial.faqs) ? initial.faqs : [],
        topics: Array.isArray(initial.topics) ? initial.topics : [],
        addFaq() {
            this.faqs.push({ question: '', answer: '' });
        },
        removeFaq(index) {
            this.faqs.splice(index, 1);
        },
        addTopic(name = '', description = '', percentage = '') {
            this.topics.push({
                name: name,
                description: description,
                percentage: percentage
            });
        },
        removeTopic(index) {
            this.topics.splice(index, 1);
        },
        loadSampleTopics() {
            this.topics = [
                {
                    name: 'Domain 1: Core Architecture & Cloud Concepts',
                    description: 'Fundamental benefits, high availability, scalability and agility',
                    percentage: '25-30%'
                },
                {
                    name: 'Domain 2: Core Services & Management Solutions',
                    description: 'Compute, networking, storage, identity and cloud management tools',
                    percentage: '35-40%'
                },
                {
                    name: 'Domain 3: Security, Privacy, Compliance & Trust',
                    description: 'Identity security, network access controls and compliance standards',
                    percentage: '30-35%'
                }
            ];
        }
    };
}
</script>
@endsection
