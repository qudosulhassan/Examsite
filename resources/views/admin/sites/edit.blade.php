@extends('layouts.admin')

@section('title', 'Website Control Center - ' . $site->name)

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="sm:flex sm:items-center sm:justify-between bg-white border border-gray-200 rounded-2xl p-6 shadow-sm">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-navy text-white font-black flex items-center justify-center text-lg shadow-md border border-cyan/30">
                {{ strtoupper(substr($site->name, 0, 2)) }}
            </div>
            <div>
                <div class="flex items-center gap-2.5">
                    <h1 class="text-2xl font-black text-navy">{{ $site->name }}</h1>
                    @if($site->is_active)
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">Active</span>
                    @else
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-gray-100 text-gray-800">Inactive</span>
                    @endif
                    @if($site->id === 1)
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-cyan/10 text-cyan-800 border border-cyan/30">Primary Root Platform</span>
                    @endif
                </div>
                <div class="text-xs text-gray-500 flex items-center gap-3 mt-1 font-mono">
                    <span>Host: {{ $site->host }}</span>
                    <span>&bull;</span>
                    <span>Code: {{ $site->code }}</span>
                    <span>&bull;</span>
                    <span>Theme: {{ ucfirst($site->default_theme) }}</span>
                </div>
            </div>
        </div>
        <div class="mt-4 sm:mt-0 flex items-center gap-3">
            <a href="http://{{ $site->host }}:8000" target="_blank" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-xl text-xs font-bold text-gray-700 hover:bg-gray-50 transition shadow-sm gap-1.5">
                <span>View Frontend</span>
                <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
            </a>
            <a href="{{ route('admin.sites.index') }}" class="inline-flex items-center px-4 py-2 bg-slate-900 rounded-xl text-xs font-bold text-white hover:bg-slate-800 transition shadow-sm">
                &larr; Back to Websites
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

    <!-- Tabbed Navigation Bar -->
    @php
        $tabs = [
            'general' => 'General',
            'domain' => 'Domains',
            'branding' => 'Branding',
            'theme' => 'Theme',
            'seo' => 'SEO & Meta',
            'exams' => 'Exam Overlays',
            'vendors' => 'Vendors & Certs',
            'pricing' => 'Products & Pricing',
            'analytics' => 'Analytics & Scripts',
            'sitemap' => 'Sitemap & Robots',
        ];
        $activeTab = request()->query('tab', $tab ?? 'general');
    @endphp

    <div class="border-b border-gray-200 bg-white rounded-t-2xl px-6 pt-3 shadow-sm">
        <nav class="-mb-px flex space-x-6 overflow-x-auto" aria-label="Tabs">
            @foreach($tabs as $key => $label)
                <a href="{{ route('admin.sites.edit', ['site' => $site->id, 'tab' => $key]) }}"
                   class="whitespace-nowrap py-3 px-1 border-b-2 font-bold text-xs uppercase tracking-wider transition {{ $activeTab === $key ? 'border-cyan text-cyan' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                    {{ $label }}
                </a>
            @endforeach
        </nav>
    </div>

    <!-- Tab Content Container -->
    <div class="bg-white border border-gray-200 rounded-b-2xl p-6 sm:p-8 shadow-sm">

        {{-- TAB 1: GENERAL --}}
        @if($activeTab === 'general')
        <form action="{{ route('admin.sites.update', $site->id) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')
            <input type="hidden" name="active_tab" value="general">

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Website Name</label>
                    <input type="text" name="name" value="{{ old('name', $site->name) }}" required class="w-full rounded-xl border-gray-300 shadow-sm focus:border-cyan focus:ring-cyan text-sm">
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Unique Code Identifier</label>
                    <input type="text" name="code" value="{{ old('code', $site->code) }}" required class="w-full rounded-xl border-gray-300 shadow-sm focus:border-cyan focus:ring-cyan text-sm font-mono">
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Default Locale</label>
                    <input type="text" name="default_locale" value="{{ old('default_locale', $site->default_locale) }}" required class="w-full rounded-xl border-gray-300 shadow-sm focus:border-cyan focus:ring-cyan text-sm font-mono">
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Contact Email</label>
                    <input type="email" name="contact_email" value="{{ old('contact_email', $site->contact_email) }}" placeholder="support@site.com" class="w-full rounded-xl border-gray-300 shadow-sm focus:border-cyan focus:ring-cyan text-sm">
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Contact Phone</label>
                    <input type="text" name="contact_phone" value="{{ old('contact_phone', $site->contact_phone) }}" placeholder="+1 (555) 000-0000" class="w-full rounded-xl border-gray-300 shadow-sm focus:border-cyan focus:ring-cyan text-sm">
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Contact Address</label>
                    <input type="text" name="contact_address" value="{{ old('contact_address', $site->contact_address) }}" placeholder="Business physical address" class="w-full rounded-xl border-gray-300 shadow-sm focus:border-cyan focus:ring-cyan text-sm">
                </div>

                <div class="sm:col-span-2 flex items-center pt-2">
                    <input type="checkbox" id="is_active" name="is_active" value="1" {{ $site->is_active ? 'checked' : '' }} class="h-4 w-4 text-cyan focus:ring-cyan border-gray-300 rounded">
                    <label for="is_active" class="ml-2 block text-sm font-bold text-gray-900">Website is active and accepting traffic</label>
                </div>
            </div>

            <div class="pt-6 border-t border-gray-200 flex justify-end">
                <button type="submit" class="px-6 py-2.5 rounded-xl shadow-sm text-sm font-bold text-white bg-cyan hover:bg-cyan/90 transition">Save General Settings</button>
            </div>
        </form>

        {{-- TAB 2: DOMAIN MANAGEMENT --}}
        @elseif($activeTab === 'domain')
        <div class="space-y-8">
            <div>
                <h3 class="text-base font-bold text-navy">Connected Domains & Hostnames</h3>
                <p class="text-xs text-gray-500 mt-1">Traffic arriving with these HTTP Host headers will automatically resolve this website's theme and content overlays.</p>
            </div>

            <div class="bg-gray-50 border border-gray-200 rounded-2xl overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase">Domain</th>
                            <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase">Type</th>
                            <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase">SSL</th>
                            <th class="px-6 py-3 text-right text-xs font-bold text-gray-500 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 text-sm bg-white">
                        @foreach($site->domains as $domain)
                        <tr>
                            <td class="px-6 py-4 font-mono font-bold text-navy">
                                {{ $domain->domain }}
                            </td>
                            <td class="px-6 py-4">
                                @if($domain->is_primary)
                                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-cyan/10 text-cyan-800 border border-cyan/30">Primary Domain</span>
                                @else
                                    <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-600">Alias</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-xs text-emerald-600 font-bold flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                    Enabled
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                @if(!$domain->is_primary)
                                <form action="{{ route('admin.sites.domains.remove', ['site' => $site->id, 'domain' => $domain->id]) }}" method="POST" class="inline" onsubmit="return confirm('Remove domain {{ $domain->domain }}?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-xs font-bold text-red-500 hover:text-red-700">Remove</button>
                                </form>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Add Domain Form -->
            <form action="{{ route('admin.sites.domains.add', $site->id) }}" method="POST" class="bg-gray-50 border border-gray-200 rounded-2xl p-6 space-y-4">
                @csrf
                <h4 class="text-sm font-bold text-navy uppercase tracking-wider">Connect Additional Domain / Alias</h4>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div class="sm:col-span-2">
                        <input type="text" name="domain" placeholder="e.g. certpasshub.com or certpass.test" required class="w-full rounded-xl border-gray-300 shadow-sm focus:border-cyan focus:ring-cyan text-sm font-mono">
                    </div>
                    <div>
                        <button type="submit" class="w-full py-2.5 px-4 bg-navy text-white rounded-xl text-xs font-bold hover:bg-slate-800 transition">Add Domain</button>
                    </div>
                </div>
            </form>
        </div>

        {{-- TAB 3: BRANDING --}}
        @elseif($activeTab === 'branding')
        <form action="{{ route('admin.sites.update', $site->id) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')
            <input type="hidden" name="active_tab" value="branding">

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Logo URL / Path</label>
                    <input type="text" name="logo" value="{{ old('logo', $site->logo) }}" placeholder="/images/logo.png" class="w-full rounded-xl border-gray-300 shadow-sm focus:border-cyan focus:ring-cyan text-sm">
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Favicon URL / Path</label>
                    <input type="text" name="favicon" value="{{ old('favicon', $site->favicon) }}" placeholder="/favicon.ico" class="w-full rounded-xl border-gray-300 shadow-sm focus:border-cyan focus:ring-cyan text-sm">
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Primary Brand Color (Hex)</label>
                    <div class="flex items-center gap-3">
                        <input type="color" value="{{ old('primary_color', $site->primary_color ?? '#00D4AA') }}" onchange="document.getElementById('primary_hex').value = this.value" class="h-10 w-12 rounded-lg border border-gray-300 cursor-pointer">
                        <input type="text" id="primary_hex" name="primary_color" value="{{ old('primary_color', $site->primary_color ?? '#00D4AA') }}" class="w-full rounded-xl border-gray-300 shadow-sm focus:border-cyan focus:ring-cyan text-sm font-mono">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Secondary Brand Color (Hex)</label>
                    <div class="flex items-center gap-3">
                        <input type="color" value="{{ old('secondary_color', $site->secondary_color ?? '#0A1628') }}" onchange="document.getElementById('secondary_hex').value = this.value" class="h-10 w-12 rounded-lg border border-gray-300 cursor-pointer">
                        <input type="text" id="secondary_hex" name="secondary_color" value="{{ old('secondary_color', $site->secondary_color ?? '#0A1628') }}" class="w-full rounded-xl border-gray-300 shadow-sm focus:border-cyan focus:ring-cyan text-sm font-mono">
                    </div>
                </div>
            </div>

            <div class="pt-6 border-t border-gray-200 flex justify-end">
                <button type="submit" class="px-6 py-2.5 rounded-xl shadow-sm text-sm font-bold text-white bg-cyan hover:bg-cyan/90 transition">Save Branding</button>
            </div>
        </form>

        {{-- TAB 4: THEME SELECTION --}}
        @elseif($activeTab === 'theme')
        <form action="{{ route('admin.sites.update', $site->id) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')
            <input type="hidden" name="active_tab" value="theme">

            <div class="space-y-4">
                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider">Select Frontend Theme Engine</label>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                    <label class="border-2 rounded-2xl p-5 cursor-pointer transition flex flex-col justify-between {{ $site->default_theme === 'default' ? 'border-cyan bg-cyan/5' : 'border-gray-200 hover:border-gray-300' }}">
                        <div>
                            <input type="radio" name="default_theme" value="default" {{ $site->default_theme === 'default' ? 'checked' : '' }} class="text-cyan focus:ring-cyan">
                            <span class="ml-2 font-bold text-navy">Default Standard Theme</span>
                            <p class="text-xs text-gray-500 mt-2">The full-featured ExamTopicsBase core layout with hero search, vendor cards, and interactive test previews.</p>
                        </div>
                        <span class="mt-4 text-[10px] font-mono text-gray-400">resources/views/themes/default</span>
                    </label>

                    <label class="border-2 rounded-2xl p-5 cursor-pointer transition flex flex-col justify-between {{ $site->default_theme === 'modern' ? 'border-cyan bg-cyan/5' : 'border-gray-200 hover:border-gray-300' }}">
                        <div>
                            <input type="radio" name="default_theme" value="modern" {{ $site->default_theme === 'modern' ? 'checked' : '' }} class="text-cyan focus:ring-cyan">
                            <span class="ml-2 font-bold text-navy">Modern Dark Theme</span>
                            <p class="text-xs text-gray-500 mt-2">High-contrast dark developer aesthetic tailored for IT certification exam dumps and study materials.</p>
                        </div>
                        <span class="mt-4 text-[10px] font-mono text-gray-400">resources/views/themes/modern</span>
                    </label>

                    <label class="border-2 rounded-2xl p-5 cursor-pointer transition flex flex-col justify-between {{ $site->default_theme === 'minimal' ? 'border-cyan bg-cyan/5' : 'border-gray-200 hover:border-gray-300' }}">
                        <div>
                            <input type="radio" name="default_theme" value="minimal" {{ $site->default_theme === 'minimal' ? 'checked' : '' }} class="text-cyan focus:ring-cyan">
                            <span class="ml-2 font-bold text-navy">Minimal Clean Theme</span>
                            <p class="text-xs text-gray-500 mt-2">Fast, lightweight, content-focused reading theme optimized for study guides and mobile browsing.</p>
                        </div>
                        <span class="mt-4 text-[10px] font-mono text-gray-400">resources/views/themes/minimal</span>
                    </label>
                </div>
            </div>

            <div class="pt-6 border-t border-gray-200 flex justify-end">
                <button type="submit" class="px-6 py-2.5 rounded-xl shadow-sm text-sm font-bold text-white bg-cyan hover:bg-cyan/90 transition">Apply Theme</button>
            </div>
        </form>

        {{-- TAB 5: SITE-SPECIFIC SEO --}}
        @elseif($activeTab === 'seo')
        <form action="{{ route('admin.sites.update', $site->id) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')
            <input type="hidden" name="active_tab" value="seo">

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <div class="sm:col-span-2">
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Default Site Title Format</label>
                    <input type="text" name="default_seo_title" value="{{ old('default_seo_title', $site->default_seo_title) }}" placeholder="e.g. {ExamCode} Practice Questions & Study Guide | CertPass Master" class="w-full rounded-xl border-gray-300 shadow-sm focus:border-cyan focus:ring-cyan text-sm">
                </div>

                <div class="sm:col-span-2">
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Default Meta Description</label>
                    <textarea name="default_meta_description" rows="3" class="w-full rounded-xl border-gray-300 shadow-sm focus:border-cyan focus:ring-cyan text-sm">{{ old('default_meta_description', $site->default_meta_description) }}</textarea>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Default H1 Title Format</label>
                    <input type="text" name="default_h1" value="{{ old('default_h1', $site->default_h1) }}" placeholder="e.g. Pass {ExamCode} with Verified Study Questions" class="w-full rounded-xl border-gray-300 shadow-sm focus:border-cyan focus:ring-cyan text-sm">
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Robots Meta Directive</label>
                    <select name="robots_directive" class="w-full rounded-xl border-gray-300 shadow-sm focus:border-cyan focus:ring-cyan text-sm">
                        <option value="index, follow" {{ $site->robots_directive === 'index, follow' ? 'selected' : '' }}>index, follow (Recommended for public sites)</option>
                        <option value="noindex, nofollow" {{ $site->robots_directive === 'noindex, nofollow' ? 'selected' : '' }}>noindex, nofollow (Staging / Private)</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Primary Keyword Intent</label>
                    <input type="text" name="primary_keyword_strategy" value="{{ old('primary_keyword_strategy', $site->primary_keyword_strategy) }}" placeholder="e.g. Practice Tests, Exam Questions, Study Notes" class="w-full rounded-xl border-gray-300 shadow-sm focus:border-cyan focus:ring-cyan text-sm">
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Canonical URL Strategy</label>
                    <select name="canonical_strategy" class="w-full rounded-xl border-gray-300 shadow-sm focus:border-cyan focus:ring-cyan text-sm">
                        <option value="self" {{ $site->canonical_strategy === 'self' ? 'selected' : '' }}>Self-referential (Mandatory for independent ranking)</option>
                        <option value="root" {{ $site->canonical_strategy === 'root' ? 'selected' : '' }}>Point to Root Platform (For syndicated mirrors)</option>
                    </select>
                </div>

                <div class="sm:col-span-2">
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Secondary Keywords & Target Themes</label>
                    <textarea name="secondary_keyword_strategy" rows="2" placeholder="e.g. braindumps, real exam questions, test engine simulator" class="w-full rounded-xl border-gray-300 shadow-sm focus:border-cyan focus:ring-cyan text-sm">{{ old('secondary_keyword_strategy', $site->secondary_keyword_strategy) }}</textarea>
                </div>
            </div>

            <div class="pt-6 border-t border-gray-200 flex justify-end">
                <button type="submit" class="px-6 py-2.5 rounded-xl shadow-sm text-sm font-bold text-white bg-cyan hover:bg-cyan/90 transition">Save SEO Strategy</button>
            </div>
        </form>

        {{-- TAB 6: EXAM OVERLAYS --}}
        @elseif($activeTab === 'exams')
        <div class="space-y-8" x-data="{ selectedExamId: '', showModal: false }">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-base font-bold text-navy">Exam Content & SEO Overlays</h3>
                    <p class="text-xs text-gray-500 mt-1">Override SEO titles, meta descriptions, and unique editorial articles for specific exams on {{ $site->name }}.</p>
                </div>
                <button type="button" @click="showModal = true" class="px-4 py-2 bg-cyan text-white text-xs font-bold rounded-xl hover:bg-cyan/90 transition shadow-sm">
                    + Configure Exam Overlay
                </button>
            </div>

            <!-- Overlays Table -->
            <div class="border border-gray-200 rounded-2xl overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase">Exam</th>
                            <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase">Custom SEO Title</th>
                            <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase">Custom Article Content</th>
                            <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase">Price Override</th>
                            <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white">
                        @forelse($site->examOverlays as $overlay)
                        <tr>
                            <td class="px-6 py-4 font-bold text-navy">
                                {{ $overlay->exam->exam_code ?? 'Exam' }}
                                <span class="block text-xs font-normal text-gray-400">{{ $overlay->exam->vendor->name ?? '' }}</span>
                            </td>
                            <td class="px-6 py-4 text-xs text-gray-700">
                                {{ $overlay->meta_title ?: '(Default Generated)' }}
                            </td>
                            <td class="px-6 py-4 text-xs">
                                @if(!empty($overlay->custom_article_content))
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800">Unique Article ({{ strlen(strip_tags($overlay->custom_article_content)) }} chars)</span>
                                @else
                                    <span class="text-gray-400">Master Catalog Default</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 font-mono text-xs">
                                {{ $overlay->custom_price_bundle ? '$' . number_format($overlay->custom_price_bundle, 2) : 'Default' }}
                            </td>
                            <td class="px-6 py-4">
                                @if($overlay->is_active)
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">Visible</span>
                                @else
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-red-100 text-red-800">Hidden</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="px-6 py-8 text-center text-gray-400 text-xs">
                                No custom exam overlays configured yet. All exams are serving the default core catalog content.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Create Overlay Modal -->
            <div x-show="showModal" class="fixed inset-0 z-50 overflow-y-auto bg-black/50 flex items-center justify-center p-4" style="display: none;">
                <div class="bg-white rounded-2xl max-w-2xl w-full p-6 space-y-6 shadow-2xl">
                    <div class="flex items-center justify-between border-b pb-4">
                        <h4 class="font-black text-navy text-lg">Configure Exam Overlay</h4>
                        <button type="button" @click="showModal = false" class="text-gray-400 hover:text-gray-600">&times;</button>
                    </div>

                    <form action="{{ route('admin.sites.exam-overlay.save', $site->id) }}" method="POST" class="space-y-4">
                        @csrf
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Select Exam</label>
                            <select name="exam_id" required class="w-full rounded-xl border-gray-300 shadow-sm text-sm">
                                @foreach($allExams as $ex)
                                    <option value="{{ $ex->id }}">{{ $ex->exam_code }} — {{ $ex->exam_name }} ({{ $ex->vendor->name ?? '' }})</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Custom SEO Title for this Site</label>
                            <input type="text" name="meta_title" placeholder="e.g. Exclusive AZ-900 Practice Questions 2026" class="w-full rounded-xl border-gray-300 shadow-sm text-sm">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Custom Meta Description</label>
                            <textarea name="meta_description" rows="2" placeholder="Custom description targeting this website's unique keyword strategy..." class="w-full rounded-xl border-gray-300 shadow-sm text-sm"></textarea>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Custom Article Content (TipTap HTML)</label>
                            <textarea name="custom_article_content" rows="4" placeholder="Unique editorial study guide text for this website to prevent Google duplicate content flags..." class="w-full rounded-xl border-gray-300 shadow-sm text-sm font-mono"></textarea>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Custom Bundle Price ($)</label>
                                <input type="number" step="0.01" name="custom_price_bundle" placeholder="Leave empty for default" class="w-full rounded-xl border-gray-300 shadow-sm text-sm font-mono">
                            </div>
                            <div class="flex items-center pt-6">
                                <input type="checkbox" id="modal_active" name="is_active" value="1" checked class="rounded text-cyan">
                                <label for="modal_active" class="ml-2 text-xs font-bold text-gray-700">Active on this site</label>
                            </div>
                        </div>

                        <div class="pt-4 border-t flex justify-end gap-3">
                            <button type="button" @click="showModal = false" class="px-4 py-2 border rounded-xl text-xs font-bold text-gray-600">Cancel</button>
                            <button type="submit" class="px-5 py-2 bg-cyan text-white text-xs font-bold rounded-xl hover:bg-cyan/90">Save Overlay</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- TAB 7: VENDORS & CERTS ASSIGNMENT --}}
        @elseif($activeTab === 'vendors')
        <form action="{{ route('admin.sites.update', $site->id) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')
            <input type="hidden" name="active_tab" value="vendors">

            <div>
                <h3 class="text-base font-bold text-navy">Vendor Visibility</h3>
                <p class="text-xs text-gray-500 mt-1">Select which certification vendors appear on this website's directory and search catalog.</p>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 pt-2">
                @php
                    $assignedVendorIds = $site->vendors->pluck('id')->toArray();
                @endphp
                @foreach($allVendors as $v)
                <label class="border border-gray-200 rounded-xl p-3 flex items-center space-x-3 cursor-pointer hover:bg-gray-50 transition">
                    <input type="checkbox" name="vendor_ids[]" value="{{ $v->id }}" {{ ($site->id === 1 || in_array($v->id, $assignedVendorIds)) ? 'checked' : '' }} class="h-4 w-4 text-cyan focus:ring-cyan rounded">
                    <span class="text-sm font-bold text-gray-800">{{ $v->name }}</span>
                </label>
                @endforeach
            </div>

            <div class="pt-6 border-t border-gray-200 flex justify-end">
                <button type="submit" class="px-6 py-2.5 rounded-xl shadow-sm text-sm font-bold text-white bg-cyan hover:bg-cyan/90 transition">Save Vendor Catalog</button>
            </div>
        </form>

        {{-- TAB 8: PRODUCTS & PRICING --}}
        @elseif($activeTab === 'pricing')
        <div class="space-y-6">
            <h3 class="text-base font-bold text-navy">Global Pricing Rule Configuration</h3>
            <p class="text-xs text-gray-500">Each website inherits the core question database but can define custom pricing multipliers or price overrides per product type.</p>

            <div class="bg-gray-50 border border-gray-200 rounded-2xl p-6 space-y-4">
                <div class="text-sm font-bold text-navy">Site Pricing Rules</div>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-2">Currency Symbol</label>
                        <input type="text" value="$ (USD)" disabled class="w-full rounded-xl bg-gray-100 border-gray-300 text-sm font-mono">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-2">Default PDF Price</label>
                        <input type="text" value="$29.99" disabled class="w-full rounded-xl bg-gray-100 border-gray-300 text-sm font-mono">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-2">Default Engine Price</label>
                        <input type="text" value="$39.99" disabled class="w-full rounded-xl bg-gray-100 border-gray-300 text-sm font-mono">
                    </div>
                </div>
            </div>
        </div>

        {{-- TAB 9: ANALYTICS & SCRIPTS --}}
        @elseif($activeTab === 'analytics')
        <form action="{{ route('admin.sites.update', $site->id) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')
            <input type="hidden" name="active_tab" value="analytics">

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Google Search Console Verification Tag</label>
                    <input type="text" name="google_search_console_code" value="{{ old('google_search_console_code', $site->google_search_console_code) }}" placeholder="google-site-verification=..." class="w-full rounded-xl border-gray-300 shadow-sm focus:border-cyan focus:ring-cyan text-sm font-mono">
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Google Analytics 4 Measurement ID</label>
                    <input type="text" name="google_analytics_id" value="{{ old('google_analytics_id', $site->google_analytics_id) }}" placeholder="G-XXXXXXXXXX" class="w-full rounded-xl border-gray-300 shadow-sm focus:border-cyan focus:ring-cyan text-sm font-mono">
                </div>

                <div class="sm:col-span-2">
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Custom Head Scripts (HTML / Scripts)</label>
                    <textarea name="custom_head_scripts" rows="3" placeholder="<!-- Site-specific tracking scripts in <head> -->" class="w-full rounded-xl border-gray-300 shadow-sm focus:border-cyan focus:ring-cyan text-sm font-mono">{{ old('custom_head_scripts', $site->custom_head_scripts) }}</textarea>
                </div>

                <div class="sm:col-span-2">
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Custom Footer Scripts</label>
                    <textarea name="custom_footer_scripts" rows="3" placeholder="<!-- Chat widgets, analytics before </body> -->" class="w-full rounded-xl border-gray-300 shadow-sm focus:border-cyan focus:ring-cyan text-sm font-mono">{{ old('custom_footer_scripts', $site->custom_footer_scripts) }}</textarea>
                </div>
            </div>

            <div class="pt-6 border-t border-gray-200 flex justify-end">
                <button type="submit" class="px-6 py-2.5 rounded-xl shadow-sm text-sm font-bold text-white bg-cyan hover:bg-cyan/90 transition">Save Tracking & Scripts</button>
            </div>
        </form>

        {{-- TAB 10: SITEMAP & ROBOTS --}}
        @elseif($activeTab === 'sitemap')
        <div class="space-y-6">
            <h3 class="text-base font-bold text-navy">Dynamic SEO Feeds</h3>
            <p class="text-xs text-gray-500">Each website dynamically serves its own robots.txt and XML sitemap generated from its active exam catalog.</p>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <div class="border border-gray-200 rounded-2xl p-6 bg-gray-50 space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold uppercase tracking-wider text-navy">XML Sitemap Feed</span>
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800">Dynamic</span>
                    </div>
                    <div class="font-mono text-xs text-gray-700 break-all bg-white p-3 rounded-xl border">
                        http://{{ $site->host }}:8000/sitemap.xml
                    </div>
                    <a href="http://{{ $site->host }}:8000/sitemap.xml" target="_blank" class="inline-flex items-center text-xs font-bold text-cyan hover:underline gap-1">
                        <span>Inspect XML Feed</span>
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                    </a>
                </div>

                <div class="border border-gray-200 rounded-2xl p-6 bg-gray-50 space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold uppercase tracking-wider text-navy">Robots.txt Directives</span>
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800">Dynamic</span>
                    </div>
                    <div class="font-mono text-xs text-gray-700 break-all bg-white p-3 rounded-xl border">
                        http://{{ $site->host }}:8000/robots.txt
                    </div>
                    <a href="http://{{ $site->host }}:8000/robots.txt" target="_blank" class="inline-flex items-center text-xs font-bold text-cyan hover:underline gap-1">
                        <span>Inspect Robots.txt</span>
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                    </a>
                </div>
            </div>
        </div>
        @endif

    </div>
</div>
@endsection

