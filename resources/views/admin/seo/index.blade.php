@extends('layouts.admin')

@section('title', 'Technical SEO Center — Exam Topics Base')

@section('styles')
<style>
    .seo-tab-btn.active {
        background-color: #0A1628 !important;
        color: #ffffff !important;
        box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1);
    }
    .seo-tab-btn.active .tab-indicator {
        background-color: #00D4AA !important;
    }
</style>
@endsection

@section('content')
<div x-data="{
    activeTab: '{{ request()->get('tab', $activeTab ?? 'overview') }}',
    switchTab(tab) {
        this.activeTab = tab;
        const url = new URL(window.location);
        url.searchParams.set('tab', tab);
        window.history.replaceState({}, '', url);
    }
}" class="space-y-6">

    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2 text-xs text-gray-500 mb-1">
                <a href="{{ route('admin.settings.index') }}" class="hover:text-navy">Settings</a>
                <span>/</span>
                <span class="font-bold text-navy">Technical SEO Center</span>
            </div>
            <div class="flex items-center space-x-3">
                <h1 class="text-2xl font-extrabold text-navy tracking-tight">Technical SEO Center</h1>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-cyan/15 text-navy border border-cyan/30">
                    11 Modules Active
                </span>
            </div>
            <p class="text-xs text-gray-500 mt-1">
                Enterprise SEO management: XML sitemaps, robots.txt, JSON-LD schemas, canonical enforcement, 301 redirects, and crawl health.
            </p>
        </div>

        <div class="flex items-center space-x-2">
            <a href="{{ url('/sitemap.xml') }}" target="_blank" class="inline-flex items-center px-3 py-2 text-xs font-bold text-navy bg-white border border-gray-300 hover:bg-gray-50 rounded-lg shadow-sm transition gap-1.5">
                <span>View Sitemap</span>
                <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
            </a>
            <a href="{{ url('/robots.txt') }}" target="_blank" class="inline-flex items-center px-3 py-2 text-xs font-bold text-navy bg-white border border-gray-300 hover:bg-gray-50 rounded-lg shadow-sm transition gap-1.5">
                <span>View Robots.txt</span>
                <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
            </a>
        </div>
    </div>

    <!-- 11 Modules Horizontal Scrollable Navigation Tabs -->
    <div class="bg-white border border-gray-200 rounded-xl p-2 shadow-sm overflow-x-auto">
        <nav class="flex space-x-1 min-w-max" aria-label="SEO Modules">
            <!-- 1. Health Overview -->
            <button type="button" @click="switchTab('overview')" :class="activeTab === 'overview' || activeTab === 'health_score' ? 'bg-navy text-white shadow-sm' : 'text-gray-600 hover:text-navy hover:bg-gray-50'" class="px-3 py-2 text-xs font-bold rounded-lg transition flex items-center gap-2">
                <span class="w-2 h-2 rounded-full" :class="activeTab === 'overview' || activeTab === 'health_score' ? 'bg-cyan' : 'bg-gray-300'"></span>
                <span>Health Score</span>
                <span class="px-1.5 py-0.2 rounded-full text-[10px] font-black {{ $healthAudit['score'] >= 80 ? 'bg-emerald-500/20 text-emerald-400' : 'bg-amber-500/20 text-amber-300' }}">
                    {{ $healthAudit['score'] }}%
                </span>
            </button>

            <!-- 2. XML Sitemap -->
            <button type="button" @click="switchTab('sitemap')" :class="activeTab === 'sitemap' ? 'bg-navy text-white shadow-sm' : 'text-gray-600 hover:text-navy hover:bg-gray-50'" class="px-3 py-2 text-xs font-bold rounded-lg transition flex items-center gap-2">
                <span class="w-2 h-2 rounded-full" :class="activeTab === 'sitemap' ? 'bg-cyan' : 'bg-gray-300'"></span>
                <span>XML Sitemap</span>
            </button>

            <!-- 3. Robots.txt -->
            <button type="button" @click="switchTab('robots')" :class="activeTab === 'robots' ? 'bg-navy text-white shadow-sm' : 'text-gray-600 hover:text-navy hover:bg-gray-50'" class="px-3 py-2 text-xs font-bold rounded-lg transition flex items-center gap-2">
                <span class="w-2 h-2 rounded-full" :class="activeTab === 'robots' ? 'bg-cyan' : 'bg-gray-300'"></span>
                <span>Robots.txt</span>
            </button>

            <!-- 4. Structured Data / Schema -->
            <button type="button" @click="switchTab('schema')" :class="activeTab === 'schema' ? 'bg-navy text-white shadow-sm' : 'text-gray-600 hover:text-navy hover:bg-gray-50'" class="px-3 py-2 text-xs font-bold rounded-lg transition flex items-center gap-2">
                <span class="w-2 h-2 rounded-full" :class="activeTab === 'schema' ? 'bg-cyan' : 'bg-gray-300'"></span>
                <span>Schema Markup</span>
            </button>

            <!-- 5. Canonical URLs -->
            <button type="button" @click="switchTab('canonical')" :class="activeTab === 'canonical' ? 'bg-navy text-white shadow-sm' : 'text-gray-600 hover:text-navy hover:bg-gray-50'" class="px-3 py-2 text-xs font-bold rounded-lg transition flex items-center gap-2">
                <span class="w-2 h-2 rounded-full" :class="activeTab === 'canonical' ? 'bg-cyan' : 'bg-gray-300'"></span>
                <span>Canonical URLs</span>
            </button>

            <!-- 6. Meta & Indexing -->
            <button type="button" @click="switchTab('meta_indexing')" :class="activeTab === 'meta_indexing' ? 'bg-navy text-white shadow-sm' : 'text-gray-600 hover:text-navy hover:bg-gray-50'" class="px-3 py-2 text-xs font-bold rounded-lg transition flex items-center gap-2">
                <span class="w-2 h-2 rounded-full" :class="activeTab === 'meta_indexing' ? 'bg-cyan' : 'bg-gray-300'"></span>
                <span>Meta &amp; Indexing</span>
            </button>

            <!-- 7. 404 & Redirects -->
            <button type="button" @click="switchTab('redirects')" :class="activeTab === 'redirects' ? 'bg-navy text-white shadow-sm' : 'text-gray-600 hover:text-navy hover:bg-gray-50'" class="px-3 py-2 text-xs font-bold rounded-lg transition flex items-center gap-2">
                <span class="w-2 h-2 rounded-full" :class="activeTab === 'redirects' ? 'bg-cyan' : 'bg-gray-300'"></span>
                <span>404 &amp; Redirects</span>
            </button>

            <!-- 8. Internal Linking & Crawl -->
            <button type="button" @click="switchTab('internal_linking')" :class="activeTab === 'internal_linking' ? 'bg-navy text-white shadow-sm' : 'text-gray-600 hover:text-navy hover:bg-gray-50'" class="px-3 py-2 text-xs font-bold rounded-lg transition flex items-center gap-2">
                <span class="w-2 h-2 rounded-full" :class="activeTab === 'internal_linking' ? 'bg-cyan' : 'bg-gray-300'"></span>
                <span>Internal Linking</span>
            </button>

            <!-- 9. Search Engines & Verification -->
            <button type="button" @click="switchTab('search_engines')" :class="activeTab === 'search_engines' ? 'bg-navy text-white shadow-sm' : 'text-gray-600 hover:text-navy hover:bg-gray-50'" class="px-3 py-2 text-xs font-bold rounded-lg transition flex items-center gap-2">
                <span class="w-2 h-2 rounded-full" :class="activeTab === 'search_engines' ? 'bg-cyan' : 'bg-gray-300'"></span>
                <span>Search Engines</span>
            </button>

            <!-- 10. Performance & Web Vitals -->
            <button type="button" @click="switchTab('performance')" :class="activeTab === 'performance' ? 'bg-navy text-white shadow-sm' : 'text-gray-600 hover:text-navy hover:bg-gray-50'" class="px-3 py-2 text-xs font-bold rounded-lg transition flex items-center gap-2">
                <span class="w-2 h-2 rounded-full" :class="activeTab === 'performance' ? 'bg-cyan' : 'bg-gray-300'"></span>
                <span>Web Vitals</span>
            </button>

            <!-- 11. SEO Defaults -->
            <button type="button" @click="switchTab('defaults')" :class="activeTab === 'defaults' ? 'bg-navy text-white shadow-sm' : 'text-gray-600 hover:text-navy hover:bg-gray-50'" class="px-3 py-2 text-xs font-bold rounded-lg transition flex items-center gap-2">
                <span class="w-2 h-2 rounded-full" :class="activeTab === 'defaults' ? 'bg-cyan' : 'bg-gray-300'"></span>
                <span>SEO Defaults</span>
            </button>
        </nav>
    </div>

    <!-- Modules Tab Panels -->
    <div>
        <!-- MODULE 6: Health Overview (Default) -->
        <div x-show="activeTab === 'overview' || activeTab === 'health_score'" x-cloak>
            @include('admin.seo.partials.health_score')
        </div>

        <!-- MODULE 1: XML Sitemap -->
        <div x-show="activeTab === 'sitemap'" x-cloak>
            @include('admin.seo.partials.sitemap')
        </div>

        <!-- MODULE 2: Robots.txt -->
        <div x-show="activeTab === 'robots'" x-cloak>
            @include('admin.seo.partials.robots')
        </div>

        <!-- MODULE 3: Structured Data / Schema -->
        <div x-show="activeTab === 'schema'" x-cloak>
            @include('admin.seo.partials.schema')
        </div>

        <!-- MODULE 4: Canonical URLs -->
        <div x-show="activeTab === 'canonical'" x-cloak>
            @include('admin.seo.partials.canonical')
        </div>

        <!-- MODULE 5: Meta & Indexing Controls -->
        <div x-show="activeTab === 'meta_indexing'" x-cloak>
            @include('admin.seo.partials.meta_indexing')
        </div>

        <!-- MODULE 7: 404 & Redirects -->
        <div x-show="activeTab === 'redirects'" x-cloak>
            @include('admin.seo.partials.redirects')
        </div>

        <!-- MODULE 8: Internal Linking & Crawl -->
        <div x-show="activeTab === 'internal_linking'" x-cloak>
            @include('admin.seo.partials.internal_linking')
        </div>

        <!-- MODULE 9: Search Engines & Verification -->
        <div x-show="activeTab === 'search_engines'" x-cloak>
            @include('admin.seo.partials.search_engines')
        </div>

        <!-- MODULE 10: Performance & Web Vitals -->
        <div x-show="activeTab === 'performance'" x-cloak>
            @include('admin.seo.partials.performance')
        </div>

        <!-- MODULE 11: SEO Defaults -->
        <div x-show="activeTab === 'defaults'" x-cloak>
            @include('admin.seo.partials.defaults')
        </div>
    </div>

</div>
@endsection
