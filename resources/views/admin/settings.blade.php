@extends('layouts.admin')

@section('title', 'Settings Center — Exam Topics Base')

@section('styles')
<style>
    /* Ensure Settings Center Grid layout displays reliably across all CSS bundler environments */
    @media (min-width: 1024px) {
        .settings-grid-layout {
            display: grid !important;
            grid-template-columns: 280px minmax(0, 1fr) !important;
            gap: 2rem !important;
            align-items: start !important;
        }
        .settings-sidebar-col {
            width: 280px !important;
            max-width: 280px !important;
            flex-shrink: 0 !important;
        }
        .settings-content-col {
            min-width: 0 !important;
            width: 100% !important;
        }
    }
    @media (max-width: 1023px) {
        .settings-grid-layout {
            display: flex !important;
            flex-direction: column !important;
            gap: 1.5rem !important;
        }
        .settings-sidebar-col, .settings-content-col {
            width: 100% !important;
        }
    }

    /* Fallback responsive grid for 2-column input rows */
    .settings-form-grid-2 {
        display: grid !important;
        grid-template-columns: repeat(1, minmax(0, 1fr)) !important;
        gap: 1.5rem !important;
    }
    @media (min-width: 768px) {
        .settings-form-grid-2 {
            grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
        }
    }
</style>
@endsection

@section('content')

<script>
function settingsCenter() {
    const tabs = [
        { id: 'general', name: 'General', badge: '' },
        { id: 'branding', name: 'Branding', badge: 'Logos' },
        { id: 'contact', name: 'Contact & Social', badge: '' },
        { id: 'seo', name: 'SEO Defaults', badge: '' },
        { id: 'promotion', name: 'Promotion Banner', badge: 'Hero' },
        { id: 'subscriptions', name: 'Subscription Plans', badge: 'Plans' },
        { id: 'email', name: 'Email Settings', badge: '' },
        { id: 'payments', name: 'Payment Gateways', badge: 'Stripe' },
        { id: 'maintenance', name: 'Maintenance Mode', badge: '' },
        { id: 'security', name: 'Security & Auth', badge: '' },
        { id: 'advanced', name: 'System & Cache', badge: 'Tools' }
    ];

    return {
        activeTab: '{{ request()->get('tab', 'general') }}',
        searchQuery: '',
        isSubmitting: false,
        hasUnsavedChanges: false,

        // SEO character counters
        seoTitle: '{{ addslashes($settings['default_seo_title'] ?? config('seo.defaults.title')) }}',
        seoDescription: '{{ addslashes($settings['default_meta_description'] ?? config('seo.defaults.description')) }}',

        // Promotion banner live preview models
        bannerActive: '{{ $settings['home_banner_active'] ?? '0' }}',
        bannerText: '{{ addslashes($settings['home_banner_text'] ?? '') }}',
        bannerCoupon: '{{ addslashes($settings['home_banner_coupon'] ?? '') }}',
        bannerButtonText: '{{ addslashes($settings['home_banner_button_text'] ?? '') }}',

        // Branding assets
        branding: {
            site_logo: '{{ $settings['site_logo'] ?? '' }}',
            site_logo_dark: '{{ $settings['site_logo_dark'] ?? '' }}',
            site_logo_light: '{{ $settings['site_logo_light'] ?? '' }}',
            site_favicon: '{{ $settings['site_favicon'] ?? '' }}',
            apple_touch_icon: '{{ $settings['apple_touch_icon'] ?? '' }}'
        },

        // Subscription Plans
        plans: @json($plans),
        planModalOpen: false,
        editingPlanIndex: null,
        newFeatureText: '',
        currentPlan: {
            name: '',
            price_monthly: 0,
            price_annual: 0,
            features: [],
            status: 'active'
        },

        // All Tabs List
        allTabs: tabs,
        visibleTabs: [...tabs],

        init() {
            this.visibleTabs = [...this.allTabs];

            // Prevent accidental tab closure if unsaved
            window.addEventListener('beforeunload', (e) => {
                if (this.hasUnsavedChanges) {
                    e.preventDefault();
                    e.returnValue = 'You have unsaved changes. Are you sure you want to leave?';
                }
            });
        },

        switchTab(tabId) {
            this.activeTab = tabId;
            const url = new URL(window.location);
            url.searchParams.set('tab', tabId);
            window.history.replaceState({}, '', url);
        },

        markDirty() {
            this.hasUnsavedChanges = true;
        },

        submitMainForm() {
            this.hasUnsavedChanges = false;
            document.getElementById('settingsMainForm').submit();
        },

        filterSettings() {
            if (!this.searchQuery) {
                this.visibleTabs = [...this.allTabs];
                return;
            }
            const q = this.searchQuery.toLowerCase();
            this.visibleTabs = this.allTabs.filter(t => 
                t.name.toLowerCase().includes(q) || 
                t.id.toLowerCase().includes(q) || 
                (t.badge && t.badge.toLowerCase().includes(q))
            );
            if (this.visibleTabs.length && !this.visibleTabs.some(t => t.id === this.activeTab)) {
                this.activeTab = this.visibleTabs[0].id;
            }
        },

        // Branding Upload
        async uploadAsset(event, type) {
            const file = event.target.files[0];
            if (!file) return;

            const formData = new FormData();
            formData.append('type', type);
            formData.append('file', file);
            formData.append('_token', '{{ csrf_token() }}');

            try {
                const response = await fetch('{{ route('admin.settings.upload-branding') }}', {
                    method: 'POST',
                    headers: { 'Accept': 'application/json' },
                    body: formData
                });
                const result = await response.json();
                if (result.success) {
                    this.branding[type] = result.url;
                    alert(result.message || 'Asset uploaded successfully.');
                } else {
                    alert(result.message || 'Upload failed.');
                }
            } catch (err) {
                alert('Network error while uploading asset.');
            }
        },

        async removeAsset(type) {
            if (!confirm('Are you sure you want to remove this custom branding asset?')) return;

            const formData = new FormData();
            formData.append('type', type);
            formData.append('_token', '{{ csrf_token() }}');

            try {
                const response = await fetch('{{ route('admin.settings.remove-branding') }}', {
                    method: 'POST',
                    headers: { 'Accept': 'application/json' },
                    body: formData
                });
                const result = await response.json();
                if (result.success) {
                    this.branding[type] = '';
                    alert('Asset removed.');
                } else {
                    alert('Network error removing asset.');
                }
            } catch (err) {
                alert('Network error removing asset.');
            }
        },

        // Subscription Plans Manager
        openPlanModal(index = null) {
            this.editingPlanIndex = index;
            if (index !== null) {
                const existing = this.plans[index];
                this.currentPlan = {
                    name: existing.name || '',
                    price_monthly: existing.price_monthly || 0,
                    price_annual: existing.price_annual || 0,
                    features: Array.isArray(existing.features) ? [...existing.features] : [],
                    status: existing.status || 'active'
                };
            } else {
                this.currentPlan = {
                    name: '',
                    price_monthly: 19,
                    price_annual: 99,
                    features: ['Access to 50 exams', 'Standard support'],
                    status: 'active'
                };
            }
            this.newFeatureText = '';
            this.planModalOpen = true;
        },

        editPlan(index) {
            this.openPlanModal(index);
        },

        duplicatePlan(index) {
            const original = this.plans[index];
            const duplicate = {
                name: original.name + ' (Copy)',
                price_monthly: original.price_monthly,
                price_annual: original.price_annual,
                features: [...(original.features || [])],
                status: original.status || 'active'
            };
            this.plans.push(duplicate);
            this.markDirty();
        },

        togglePlanStatus(index) {
            this.plans[index].status = this.plans[index].status === 'disabled' ? 'active' : 'disabled';
            this.markDirty();
        },

        deletePlan(index) {
            if (confirm('Delete this subscription plan? Existing active subscribers will not be affected.')) {
                this.plans.splice(index, 1);
                this.markDirty();
            }
        },

        addFeature() {
            if (!this.newFeatureText.trim()) return;
            this.currentPlan.features.push(this.newFeatureText.trim());
            this.newFeatureText = '';
        },

        removeFeature(index) {
            this.currentPlan.features.splice(index, 1);
        },

        savePlan() {
            if (!this.currentPlan.name.trim()) {
                alert('Plan name is required.');
                return;
            }
            if (this.currentPlan.price_monthly < 0 || this.currentPlan.price_annual < 0) {
                alert('Prices cannot be negative.');
                return;
            }

            if (this.editingPlanIndex !== null) {
                this.plans[this.editingPlanIndex] = { ...this.currentPlan };
            } else {
                this.plans.push({ ...this.currentPlan });
            }

            this.planModalOpen = false;
            this.markDirty();
        }
    };
}
</script>

<div x-data="settingsCenter()" class="space-y-8 pb-24">

    <!-- Top Workspace Header (ExamTopicsBase Dark Navy & Teal Design) -->
    <div class="bg-white border border-gray-200 rounded-xl p-6 shadow-sm">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
            <!-- Left: Breadcrumb & Title -->
            <div class="space-y-1">
                <div class="flex items-center space-x-2 text-xs font-semibold uppercase tracking-wider text-gray-500">
                    <a href="{{ route('admin.dashboard') }}" class="hover:text-navy transition">Dashboard</a>
                    <span>/</span>
                    <span class="text-navy font-bold">Settings</span>
                </div>
                <div class="flex flex-wrap items-center gap-3">
                    <h1 class="text-2xl sm:text-3xl font-black text-navy tracking-tight font-heading">
                        Settings Center
                    </h1>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-cyan/10 text-cyan border border-cyan/30">
                        Production Ready
                    </span>
                </div>
                <p class="text-xs text-gray-500">
                    Manage global platform settings, brand identity, subscriptions, SEO defaults, and payment gateways.
                </p>
            </div>

            <!-- Right: Meta Status & Fast Actions -->
            <div class="flex flex-wrap items-center gap-3">
                <div class="text-right text-xs text-gray-500 hidden sm:block">
                    <div>Last updated: <span class="font-bold text-navy">{{ $lastUpdatedTime ? $lastUpdatedTime->diffForHumans() : 'Never' }}</span></div>
                    <div class="text-[11px] text-gray-400">By: {{ $lastUpdatedBy }}</div>
                </div>

                <button type="button" @click="submitMainForm()" :disabled="isSubmitting" class="inline-flex items-center gap-2 bg-navy hover:bg-navy/90 text-white font-bold text-xs uppercase tracking-wider px-5 py-2.5 rounded-lg shadow transition disabled:opacity-50">
                    <svg class="w-4 h-4 text-cyan" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    <span x-text="isSubmitting ? 'Saving...' : 'Save Settings'">Save Settings</span>
                </button>
            </div>
        </div>

        <!-- Search Settings Bar & Quick Jump -->
        <div class="mt-6 pt-5 border-t border-gray-100 flex flex-col sm:flex-row items-center justify-between gap-4">
            <div class="relative w-full sm:w-80">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
                <input type="text" x-model="searchQuery" @input="filterSettings()" placeholder="Search settings (e.g., logo, SEO, stripe)..." class="w-full pl-9 pr-4 py-1.5 text-xs rounded-lg border-gray-200 focus:border-cyan focus:ring-cyan">
                <button type="button" x-show="searchQuery" @click="searchQuery = ''; filterSettings()" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>

            <!-- Unsaved changes warning indicator -->
            <div x-show="hasUnsavedChanges" x-cloak class="flex items-center gap-2 bg-amber-50 border border-amber-200 text-amber-800 text-xs px-3 py-1.5 rounded-lg animate-pulse">
                <svg class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                <span class="font-semibold">You have unsaved changes!</span>
            </div>
        </div>
    </div>

    <!-- Alert / Flash Messages -->
    @if(session('success'))
        <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-bold p-4 rounded-xl flex items-center gap-3 shadow-sm">
            <svg class="w-5 h-5 text-emerald-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
            <div>
                <p class="font-black text-sm">{{ session('success') }}</p>
                <p class="text-[11px] font-normal text-emerald-700">All changes have been committed and cached configuration refreshed.</p>
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-50 border border-red-200 text-red-800 text-xs font-bold p-4 rounded-xl flex items-center gap-3 shadow-sm">
            <svg class="w-5 h-5 text-red-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            <div>
                <p class="font-black text-sm">{{ session('error') }}</p>
            </div>
        </div>
    @endif

    @if(isset($errors) && $errors->any())
        <div class="bg-red-50 border-l-4 border-red-500 text-red-800 p-4 rounded-xl shadow-sm text-xs">
            <div class="font-bold flex items-center gap-2 mb-2 text-sm text-red-900">
                <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                Please correct the following configuration errors:
            </div>
            <ul class="list-disc list-inside space-y-1 text-red-700">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Main Tab Navigation Layout -->
    <div class="settings-grid-layout grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
        <!-- Tab Navigation Sidebar (Desktop & Mobile Dropdown) -->
        <div class="settings-sidebar-col lg:col-span-3">
            <div class="bg-white border border-gray-200 rounded-xl p-3 shadow-sm sticky top-6 space-y-1">
                <div class="px-3 py-2 text-[10px] font-black uppercase tracking-wider text-gray-400">Settings Sections</div>
                
                <template x-for="tab in visibleTabs" :key="tab.id">
                    <button type="button" @click="switchTab(tab.id)"
                            class="w-full flex items-center justify-between px-3 py-2.5 rounded-lg text-xs font-bold transition text-left"
                            :class="activeTab === tab.id ? 'bg-navy text-white shadow-sm ring-1 ring-navy' : 'text-gray-600 hover:bg-gray-50 hover:text-navy'">
                        <div class="flex items-center gap-2.5">
                            <span class="w-2 h-2 rounded-full" :class="activeTab === tab.id ? 'bg-cyan' : 'bg-gray-300'"></span>
                            <span x-text="tab.name"></span>
                        </div>
                        <span x-show="tab.badge" x-text="tab.badge" class="px-1.5 py-0.5 text-[9px] rounded font-bold uppercase"
                              :class="activeTab === tab.id ? 'bg-cyan/20 text-cyan' : 'bg-gray-100 text-gray-500'"></span>
                    </button>
                </template>
            </div>
        </div>

        <!-- Tab Content Area (Main Form) -->
        <div class="settings-content-col lg:col-span-9 space-y-6">
            <form id="settingsMainForm" action="{{ route('admin.settings.update') }}" method="POST" @submit="isSubmitting = true" class="space-y-6">
                @csrf
                <input type="hidden" name="active_tab" :value="activeTab">

                <!-- TAB 1: GENERAL -->
                <div x-show="activeTab === 'general'" x-cloak class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden" id="section-general">
                    <div class="border-b border-gray-100 px-6 py-4 bg-gray-50/50 flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <span class="flex items-center justify-center w-7 h-7 rounded-md bg-navy text-white text-xs font-black">01</span>
                            <div>
                                <h3 class="text-sm font-bold text-navy uppercase tracking-wide">General Settings</h3>
                                <p class="text-xs text-gray-500">Core platform details, naming, default currency, and timezone.</p>
                            </div>
                        </div>
                    </div>

                    <div class="p-6 space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="site_name" class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-1.5">
                                    Site Name <span class="text-red-500">*</span>
                                </label>
                                <input type="text" name="site_name" id="site_name" @input="markDirty()" value="{{ old('site_name', $settings['site_name'] ?? 'Exam Topics Base') }}" required class="w-full text-sm border-gray-300 rounded-lg focus:border-cyan focus:ring-cyan font-medium">
                                <p class="text-[11px] text-gray-400 mt-1">Displayed in title bars, emails, and footer copyright.</p>
                            </div>

                            <div>
                                <label for="site_tagline" class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-1.5">
                                    Site Tagline
                                </label>
                                <input type="text" name="site_tagline" id="site_tagline" @input="markDirty()" value="{{ old('site_tagline', $settings['site_tagline'] ?? '') }}" class="w-full text-sm border-gray-300 rounded-lg focus:border-cyan focus:ring-cyan font-medium">
                                <p class="text-[11px] text-gray-400 mt-1">Marketing slogan displayed on the homepage and meta tags.</p>
                            </div>

                            <div>
                                <label for="site_url" class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-1.5">
                                    Canonical Site URL
                                </label>
                                <input type="url" name="site_url" id="site_url" @input="markDirty()" value="{{ old('site_url', $settings['site_url'] ?? config('app.url', 'http://127.0.0.1:8000')) }}" class="w-full text-sm border-gray-300 rounded-lg focus:border-cyan focus:ring-cyan font-mono text-xs">
                                <p class="text-[11px] text-gray-400 mt-1">Base domain URL for public asset generation and sitemaps.</p>
                            </div>

                            <div>
                                <label for="default_currency" class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-1.5">
                                    Default Currency
                                </label>
                                <select name="default_currency" id="default_currency" @change="markDirty()" class="w-full text-sm border-gray-300 rounded-lg focus:border-cyan focus:ring-cyan">
                                    <option value="USD" {{ old('default_currency', $settings['default_currency'] ?? 'USD') === 'USD' ? 'selected' : '' }}>USD ($ - United States Dollar)</option>
                                    <option value="EUR" {{ old('default_currency', $settings['default_currency'] ?? 'USD') === 'EUR' ? 'selected' : '' }}>EUR (€ - Euro)</option>
                                    <option value="GBP" {{ old('default_currency', $settings['default_currency'] ?? 'USD') === 'GBP' ? 'selected' : '' }}>GBP (£ - British Pound)</option>
                                    <option value="CAD" {{ old('default_currency', $settings['default_currency'] ?? 'USD') === 'CAD' ? 'selected' : '' }}>CAD ($ - Canadian Dollar)</option>
                                    <option value="AUD" {{ old('default_currency', $settings['default_currency'] ?? 'USD') === 'AUD' ? 'selected' : '' }}>AUD ($ - Australian Dollar)</option>
                                </select>
                            </div>

                            <div>
                                <label for="default_timezone" class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-1.5">
                                    Default Timezone
                                </label>
                                <select name="default_timezone" id="default_timezone" @change="markDirty()" class="w-full text-sm border-gray-300 rounded-lg focus:border-cyan focus:ring-cyan">
                                    <option value="UTC" {{ old('default_timezone', $settings['default_timezone'] ?? 'UTC') === 'UTC' ? 'selected' : '' }}>UTC (Coordinated Universal Time)</option>
                                    <option value="America/New_York" {{ old('default_timezone', $settings['default_timezone'] ?? 'UTC') === 'America/New_York' ? 'selected' : '' }}>America/New_York (EST / EDT)</option>
                                    <option value="America/Chicago" {{ old('default_timezone', $settings['default_timezone'] ?? 'UTC') === 'America/Chicago' ? 'selected' : '' }}>America/Chicago (CST / CDT)</option>
                                    <option value="America/Los_Angeles" {{ old('default_timezone', $settings['default_timezone'] ?? 'UTC') === 'America/Los_Angeles' ? 'selected' : '' }}>America/Los_Angeles (PST / PDT)</option>
                                    <option value="Europe/London" {{ old('default_timezone', $settings['default_timezone'] ?? 'UTC') === 'Europe/London' ? 'selected' : '' }}>Europe/London (GMT / BST)</option>
                                    <option value="Asia/Dubai" {{ old('default_timezone', $settings['default_timezone'] ?? 'UTC') === 'Asia/Dubai' ? 'selected' : '' }}>Asia/Dubai (GST)</option>
                                    <option value="Asia/Karachi" {{ old('default_timezone', $settings['default_timezone'] ?? 'UTC') === 'Asia/Karachi' ? 'selected' : '' }}>Asia/Karachi (PKT)</option>
                                </select>
                            </div>

                            <div>
                                <label for="contact_email" class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-1.5">
                                    Primary Contact Email <span class="text-red-500">*</span>
                                </label>
                                <input type="email" name="contact_email" id="contact_email" @input="markDirty()" value="{{ old('contact_email', $settings['contact_email'] ?? 'contact@examtopicsbase.com') }}" required class="w-full text-sm border-gray-300 rounded-lg focus:border-cyan focus:ring-cyan font-medium">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- TAB 2: BRANDING (Logos & Favicon) -->
                <div x-show="activeTab === 'branding'" x-cloak class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden" id="section-branding">
                    <div class="border-b border-gray-100 px-6 py-4 bg-gray-50/50 flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <span class="flex items-center justify-center w-7 h-7 rounded-md bg-navy text-white text-xs font-black">02</span>
                            <div>
                                <h3 class="text-sm font-bold text-navy uppercase tracking-wide">Brand & Visual Identity</h3>
                                <p class="text-xs text-gray-500">Upload primary logo, dark/light variations, and browser favicons.</p>
                            </div>
                        </div>
                    </div>

                    <div class="p-6 space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Primary Site Logo -->
                            <div class="p-4 border border-gray-200 rounded-xl bg-gray-50/50 space-y-3">
                                <div class="flex items-center justify-between">
                                    <label class="text-xs font-bold text-navy uppercase tracking-wide">Primary Logo</label>
                                    <span class="text-[10px] text-gray-400 font-mono">PNG, SVG, WEBP (Max 3MB)</span>
                                </div>
                                
                                <div class="bg-navy p-4 rounded-lg flex items-center justify-center min-h-[90px] relative group">
                                    <img :src="branding.site_logo || '{{ asset('images/logo.png') }}'" alt="Site Logo Preview" class="h-10 max-w-[200px] object-contain">
                                </div>

                                <div class="flex items-center gap-2 pt-1">
                                    <label class="cursor-pointer inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-bold text-white bg-navy hover:bg-navy/90 rounded-md transition shadow-sm">
                                        <svg class="w-3.5 h-3.5 text-cyan" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" /></svg>
                                        <span>Upload New</span>
                                        <input type="file" @change="uploadAsset($event, 'site_logo')" accept="image/*" class="hidden">
                                    </label>
                                    <button type="button" x-show="branding.site_logo" @click="removeAsset('site_logo')" class="px-3 py-1.5 text-xs font-bold text-red-600 hover:bg-red-50 rounded-md border border-red-200 transition">
                                        Remove
                                    </button>
                                </div>
                            </div>

                            <!-- Site Favicon -->
                            <div class="p-4 border border-gray-200 rounded-xl bg-gray-50/50 space-y-3">
                                <div class="flex items-center justify-between">
                                    <label class="text-xs font-bold text-navy uppercase tracking-wide">Browser Favicon</label>
                                    <span class="text-[10px] text-gray-400 font-mono">PNG, ICO (32x32, 64x64)</span>
                                </div>
                                
                                <div class="bg-white border border-gray-200 p-4 rounded-lg flex items-center justify-center min-h-[90px]">
                                    <img :src="branding.site_favicon || '{{ asset('favicon-32x32.png') }}'" alt="Favicon Preview" class="w-8 h-8 object-contain">
                                </div>

                                <div class="flex items-center gap-2 pt-1">
                                    <label class="cursor-pointer inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-bold text-white bg-navy hover:bg-navy/90 rounded-md transition shadow-sm">
                                        <svg class="w-3.5 h-3.5 text-cyan" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" /></svg>
                                        <span>Upload Favicon</span>
                                        <input type="file" @change="uploadAsset($event, 'site_favicon')" accept="image/png,image/x-icon,image/svg+xml" class="hidden">
                                    </label>
                                    <button type="button" x-show="branding.site_favicon" @click="removeAsset('site_favicon')" class="px-3 py-1.5 text-xs font-bold text-red-600 hover:bg-red-50 rounded-md border border-red-200 transition">
                                        Remove
                                    </button>
                                </div>
                            </div>

                            <!-- Dark Logo -->
                            <div class="p-4 border border-gray-200 rounded-xl bg-gray-50/50 space-y-3">
                                <div class="flex items-center justify-between">
                                    <label class="text-xs font-bold text-navy uppercase tracking-wide">Dark Logo (Light backgrounds)</label>
                                    <span class="text-[10px] text-gray-400 font-mono">PNG, SVG (Max 3MB)</span>
                                </div>
                                
                                <div class="bg-white border border-gray-200 p-4 rounded-lg flex items-center justify-center min-h-[90px]">
                                    <img :src="branding.site_logo_dark || branding.site_logo || '{{ asset('images/logo.png') }}'" alt="Dark Logo Preview" class="h-10 max-w-[200px] object-contain">
                                </div>

                                <div class="flex items-center gap-2 pt-1">
                                    <label class="cursor-pointer inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-bold text-white bg-navy hover:bg-navy/90 rounded-md transition shadow-sm">
                                        <svg class="w-3.5 h-3.5 text-cyan" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" /></svg>
                                        <span>Upload Logo</span>
                                        <input type="file" @change="uploadAsset($event, 'site_logo_dark')" accept="image/*" class="hidden">
                                    </label>
                                    <button type="button" x-show="branding.site_logo_dark" @click="removeAsset('site_logo_dark')" class="px-3 py-1.5 text-xs font-bold text-red-600 hover:bg-red-50 rounded-md border border-red-200 transition">
                                        Remove
                                    </button>
                                </div>
                            </div>

                            <!-- Apple Touch Icon -->
                            <div class="p-4 border border-gray-200 rounded-xl bg-gray-50/50 space-y-3">
                                <div class="flex items-center justify-between">
                                    <label class="text-xs font-bold text-navy uppercase tracking-wide">Apple Touch Icon (180x180)</label>
                                    <span class="text-[10px] text-gray-400 font-mono">Square PNG</span>
                                </div>
                                
                                <div class="bg-white border border-gray-200 p-4 rounded-lg flex items-center justify-center min-h-[90px]">
                                    <img :src="branding.apple_touch_icon || '{{ asset('apple-touch-icon.png') }}'" alt="Apple Touch Icon Preview" class="w-12 h-12 rounded-xl object-contain shadow-sm border border-gray-200">
                                </div>

                                <div class="flex items-center gap-2 pt-1">
                                    <label class="cursor-pointer inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-bold text-white bg-navy hover:bg-navy/90 rounded-md transition shadow-sm">
                                        <svg class="w-3.5 h-3.5 text-cyan" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" /></svg>
                                        <span>Upload Icon</span>
                                        <input type="file" @change="uploadAsset($event, 'apple_touch_icon')" accept="image/png" class="hidden">
                                    </label>
                                    <button type="button" x-show="branding.apple_touch_icon" @click="removeAsset('apple_touch_icon')" class="px-3 py-1.5 text-xs font-bold text-red-600 hover:bg-red-50 rounded-md border border-red-200 transition">
                                        Remove
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- TAB 3: CONTACT & SOCIAL -->
                <div x-show="activeTab === 'contact'" x-cloak class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden" id="section-contact">
                    <div class="border-b border-gray-100 px-6 py-4 bg-gray-50/50 flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <span class="flex items-center justify-center w-7 h-7 rounded-md bg-navy text-white text-xs font-black">03</span>
                            <div>
                                <h3 class="text-sm font-bold text-navy uppercase tracking-wide">Contact &amp; Social Links</h3>
                                <p class="text-xs text-gray-500">Customer communication channels and official social media profile URLs.</p>
                            </div>
                        </div>
                    </div>

                    <div class="p-6 space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="support_email" class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-1.5">
                                    Support Email Address
                                </label>
                                <input type="email" name="support_email" id="support_email" @input="markDirty()" value="{{ old('support_email', $settings['support_email'] ?? 'support@examtopicsbase.com') }}" class="w-full text-sm border-gray-300 rounded-lg focus:border-cyan focus:ring-cyan">
                            </div>

                            <div>
                                <label for="contact_phone" class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-1.5">
                                    Phone Number
                                </label>
                                <input type="text" name="contact_phone" id="contact_phone" @input="markDirty()" value="{{ old('contact_phone', $settings['contact_phone'] ?? '') }}" placeholder="+1 (800) 123-4567" class="w-full text-sm border-gray-300 rounded-lg focus:border-cyan focus:ring-cyan">
                            </div>

                            <div>
                                <label for="contact_whatsapp" class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-1.5">
                                    WhatsApp Support Number / Link
                                </label>
                                <input type="text" name="contact_whatsapp" id="contact_whatsapp" @input="markDirty()" value="{{ old('contact_whatsapp', $settings['contact_whatsapp'] ?? '') }}" placeholder="+1234567890" class="w-full text-sm border-gray-300 rounded-lg focus:border-cyan focus:ring-cyan">
                            </div>

                            <div>
                                <label for="business_address" class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-1.5">
                                    Business / Operational Address
                                </label>
                                <input type="text" name="business_address" id="business_address" @input="markDirty()" value="{{ old('business_address', $settings['business_address'] ?? '') }}" placeholder="123 Tech Center Blvd, San Francisco, CA" class="w-full text-sm border-gray-300 rounded-lg focus:border-cyan focus:ring-cyan">
                            </div>
                        </div>

                        <!-- Social Media Links (Only saved & displayed if valid URL) -->
                        <div class="pt-4 border-t border-gray-100">
                            <h4 class="text-xs font-bold text-navy uppercase tracking-wider mb-4">Official Social Profiles</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-[11px] font-bold text-gray-600 mb-1">Facebook URL</label>
                                    <input type="url" name="social_facebook" @input="markDirty()" value="{{ old('social_facebook', $settings['social_facebook'] ?? '') }}" placeholder="https://facebook.com/yourbrand" class="w-full text-xs border-gray-300 rounded-lg focus:border-cyan focus:ring-cyan">
                                </div>
                                <div>
                                    <label class="block text-[11px] font-bold text-gray-600 mb-1">X (Twitter) URL</label>
                                    <input type="url" name="social_twitter" @input="markDirty()" value="{{ old('social_twitter', $settings['social_twitter'] ?? '') }}" placeholder="https://x.com/yourbrand" class="w-full text-xs border-gray-300 rounded-lg focus:border-cyan focus:ring-cyan">
                                </div>
                                <div>
                                    <label class="block text-[11px] font-bold text-gray-600 mb-1">LinkedIn URL</label>
                                    <input type="url" name="social_linkedin" @input="markDirty()" value="{{ old('social_linkedin', $settings['social_linkedin'] ?? '') }}" placeholder="https://linkedin.com/company/yourbrand" class="w-full text-xs border-gray-300 rounded-lg focus:border-cyan focus:ring-cyan">
                                </div>
                                <div>
                                    <label class="block text-[11px] font-bold text-gray-600 mb-1">Instagram URL</label>
                                    <input type="url" name="social_instagram" @input="markDirty()" value="{{ old('social_instagram', $settings['social_instagram'] ?? '') }}" placeholder="https://instagram.com/yourbrand" class="w-full text-xs border-gray-300 rounded-lg focus:border-cyan focus:ring-cyan">
                                </div>
                                <div>
                                    <label class="block text-[11px] font-bold text-gray-600 mb-1">YouTube Channel URL</label>
                                    <input type="url" name="social_youtube" @input="markDirty()" value="{{ old('social_youtube', $settings['social_youtube'] ?? '') }}" placeholder="https://youtube.com/@yourbrand" class="w-full text-xs border-gray-300 rounded-lg focus:border-cyan focus:ring-cyan">
                                </div>
                                <div>
                                    <label class="block text-[11px] font-bold text-gray-600 mb-1">Telegram Community URL</label>
                                    <input type="url" name="social_telegram" @input="markDirty()" value="{{ old('social_telegram', $settings['social_telegram'] ?? '') }}" placeholder="https://t.me/yourcommunity" class="w-full text-xs border-gray-300 rounded-lg focus:border-cyan focus:ring-cyan">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- TAB 4: SEO SETTINGS -->
                <div x-show="activeTab === 'seo'" x-cloak class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden" id="section-seo">
                    <div class="border-b border-gray-100 px-6 py-4 bg-gray-50/50 flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <span class="flex items-center justify-center w-7 h-7 rounded-md bg-navy text-white text-xs font-black">04</span>
                            <div>
                                <h3 class="text-sm font-bold text-navy uppercase tracking-wide">SEO &amp; Search Engine Defaults</h3>
                                <p class="text-xs text-gray-500">Global fallback SEO metadata. Exam and blog specific settings will ALWAYS take priority.</p>
                            </div>
                        </div>
                    </div>

                    <div class="p-6 space-y-6">
                        <!-- Google SERP Live Preview -->
                        <div class="bg-gray-50 border border-gray-200 rounded-xl p-4.5 space-y-2">
                            <div class="text-[10px] font-bold uppercase tracking-wider text-gray-400">Live Google Search Preview</div>
                            <div class="bg-white p-4 rounded-lg border border-gray-200 shadow-sm max-w-xl space-y-1">
                                <div class="text-xs text-[#202124] flex items-center gap-1.5 font-sans">
                                    <span class="w-4 h-4 rounded-full bg-cyan/20 inline-flex items-center justify-center text-[10px] font-bold text-navy">E</span>
                                    <span class="text-xs text-[#4d5156]">{{ $settings['site_url'] ?? 'https://examtopicsbase.com' }}</span>
                                </div>
                                <h4 class="text-base text-[#1a0dab] hover:underline font-medium cursor-pointer truncate" x-text="seoTitle || 'Exam Topics Base - Pass Your IT Certification Exam First Attempt'"></h4>
                                <p class="text-xs text-[#4d5156] line-clamp-2 leading-relaxed" x-text="seoDescription || 'Prepare for any IT certification exam with Exam Topics Base. Download verified PDF dumps or practice online with our test engine.'"></p>
                            </div>
                        </div>

                        <div class="space-y-4">
                            <div>
                                <div class="flex items-center justify-between mb-1">
                                    <label for="default_seo_title" class="block text-xs font-bold text-gray-700 uppercase tracking-wide">Default SEO Title</label>
                                    <span class="text-[10px] text-gray-400 font-mono"><span x-text="seoTitle.length"></span> / 60 chars</span>
                                </div>
                                <input type="text" name="default_seo_title" id="default_seo_title" x-model="seoTitle" @input="markDirty()" value="{{ old('default_seo_title', $settings['default_seo_title'] ?? config('seo.defaults.title')) }}" class="w-full text-sm border-gray-300 rounded-lg focus:border-cyan focus:ring-cyan font-medium">
                            </div>

                            <div>
                                <div class="flex items-center justify-between mb-1">
                                    <label for="default_meta_description" class="block text-xs font-bold text-gray-700 uppercase tracking-wide">Default Meta Description</label>
                                    <span class="text-[10px] text-gray-400 font-mono"><span x-text="seoDescription.length"></span> / 160 chars</span>
                                </div>
                                <textarea name="default_meta_description" id="default_meta_description" x-model="seoDescription" @input="markDirty()" rows="3" class="w-full text-sm border-gray-300 rounded-lg focus:border-cyan focus:ring-cyan">{{ old('default_meta_description', $settings['default_meta_description'] ?? config('seo.defaults.description')) }}</textarea>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label for="default_meta_keywords" class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-1.5">Default Keywords</label>
                                    <input type="text" name="default_meta_keywords" id="default_meta_keywords" @input="markDirty()" value="{{ old('default_meta_keywords', $settings['default_meta_keywords'] ?? config('seo.defaults.keywords')) }}" class="w-full text-sm border-gray-300 rounded-lg focus:border-cyan focus:ring-cyan">
                                </div>

                                <div>
                                    <label for="robots_setting" class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-1.5">Robots Meta Directive</label>
                                    <select name="robots_setting" id="robots_setting" @change="markDirty()" class="w-full text-sm border-gray-300 rounded-lg focus:border-cyan focus:ring-cyan">
                                        <option value="index, follow" {{ old('robots_setting', $settings['robots_setting'] ?? 'index, follow') === 'index, follow' ? 'selected' : '' }}>index, follow (Allow search indexing - Recommended for Production)</option>
                                        <option value="noindex, nofollow" {{ old('robots_setting', $settings['robots_setting'] ?? '') === 'noindex, nofollow' ? 'selected' : '' }}>noindex, nofollow (Block search engines - Staging/Dev)</option>
                                        <option value="noindex, follow" {{ old('robots_setting', $settings['robots_setting'] ?? '') === 'noindex, follow' ? 'selected' : '' }}>noindex, follow</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- TAB 5: PROMOTION BANNER -->
                <div x-show="activeTab === 'promotion'" x-cloak class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden" id="section-promotion">
                    <div class="border-b border-gray-100 px-6 py-4 bg-gray-50/50 flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <span class="flex items-center justify-center w-7 h-7 rounded-md bg-navy text-white text-xs font-black">05</span>
                            <div>
                                <h3 class="text-sm font-bold text-navy uppercase tracking-wide">Header Promotion Banner</h3>
                                <p class="text-xs text-gray-500">Display global announcement banners, sales timers, and discount coupons.</p>
                            </div>
                        </div>
                    </div>

                    <div class="p-6 space-y-6">
                        <!-- Live Banner Preview Component -->
                        <div class="space-y-2">
                            <div class="text-[10px] font-bold uppercase tracking-wider text-gray-400">Live Header Preview</div>
                            <div class="bg-gradient-to-r from-orange to-red-500 text-white py-2.5 px-4 text-center text-xs sm:text-sm shadow-md rounded-xl flex items-center justify-center flex-wrap gap-2 transition-all"
                                 :class="bannerActive == '1' ? 'opacity-100' : 'opacity-40 grayscale'">
                                <span class="font-bold" x-text="bannerText || '🔥 FLASH SALE! Use coupon NINJA50 for 50% off all bundles!'"></span>
                                <template x-if="bannerCoupon">
                                    <span class="bg-white text-orange px-2 py-0.5 rounded font-mono font-black text-xs mx-1" x-text="bannerCoupon"></span>
                                </template>
                                <template x-if="bannerButtonText">
                                    <span class="inline-flex items-center text-[10px] uppercase font-black bg-navy text-white px-2.5 py-1 rounded shadow-sm hover:bg-black transition ml-2" x-text="bannerButtonText"></span>
                                </template>
                            </div>
                            <div class="text-[11px] text-gray-400 italic text-center" x-show="bannerActive != '1'">
                                (Banner is currently set to Inactive / Hidden)
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 pt-2">
                            <div class="md:col-span-2">
                                <label for="home_banner_text" class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-1.5">
                                    Banner Message <span class="text-red-500">*</span>
                                </label>
                                <input type="text" name="home_banner_text" id="home_banner_text" x-model="bannerText" @input="markDirty()" value="{{ old('home_banner_text', $settings['home_banner_text'] ?? '') }}" placeholder="e.g. FLASH SALE! Get 50% off all certification packs" class="w-full text-sm border-gray-300 rounded-lg focus:border-cyan focus:ring-cyan font-medium">
                            </div>

                            <div>
                                <label for="home_banner_active" class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-1.5">
                                    Banner Status
                                </label>
                                <select name="home_banner_active" id="home_banner_active" x-model="bannerActive" @change="markDirty()" class="w-full text-sm border-gray-300 rounded-lg focus:border-cyan focus:ring-cyan font-bold">
                                    <option value="0" {{ old('home_banner_active', $settings['home_banner_active'] ?? '0') === '0' ? 'selected' : '' }}>Inactive / Hidden</option>
                                    <option value="1" {{ old('home_banner_active', $settings['home_banner_active'] ?? '0') === '1' ? 'selected' : '' }}>Active / Visible</option>
                                </select>
                            </div>

                            <div>
                                <label for="home_banner_coupon" class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-1.5">
                                    Highlight Coupon Code (Optional)
                                </label>
                                <input type="text" name="home_banner_coupon" id="home_banner_coupon" x-model="bannerCoupon" @input="markDirty()" value="{{ old('home_banner_coupon', $settings['home_banner_coupon'] ?? '') }}" placeholder="e.g. NINJA50" class="w-full text-sm border-gray-300 rounded-lg focus:border-cyan focus:ring-cyan font-mono uppercase font-bold text-orange">
                            </div>

                            <div>
                                <label for="home_banner_button_text" class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-1.5">
                                    Button Text (Optional)
                                </label>
                                <input type="text" name="home_banner_button_text" id="home_banner_button_text" x-model="bannerButtonText" @input="markDirty()" value="{{ old('home_banner_button_text', $settings['home_banner_button_text'] ?? '') }}" placeholder="e.g. Shop Now" class="w-full text-sm border-gray-300 rounded-lg focus:border-cyan focus:ring-cyan">
                            </div>

                            <div>
                                <label for="home_banner_link" class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-1.5">
                                    Target Action URL
                                </label>
                                <input type="text" name="home_banner_link" id="home_banner_link" @input="markDirty()" value="{{ old('home_banner_link', $settings['home_banner_link'] ?? '#') }}" placeholder="e.g. /vendors" class="w-full text-sm border-gray-300 rounded-lg focus:border-cyan focus:ring-cyan">
                            </div>

                            <div>
                                <label for="home_banner_start_date" class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-1.5">
                                    Campaign Start Date (Optional)
                                </label>
                                <input type="datetime-local" name="home_banner_start_date" id="home_banner_start_date" @input="markDirty()" value="{{ old('home_banner_start_date', isset($settings['home_banner_start_date']) ? date('Y-m-dTH:i', strtotime($settings['home_banner_start_date'])) : '') }}" class="w-full text-xs border-gray-300 rounded-lg focus:border-cyan focus:ring-cyan">
                            </div>

                            <div>
                                <label for="home_banner_end_date" class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-1.5">
                                    Campaign End Date (Auto-expire)
                                </label>
                                <input type="datetime-local" name="home_banner_end_date" id="home_banner_end_date" @input="markDirty()" value="{{ old('home_banner_end_date', isset($settings['home_banner_end_date']) ? date('Y-m-dTH:i', strtotime($settings['home_banner_end_date'])) : '') }}" class="w-full text-xs border-gray-300 rounded-lg focus:border-cyan focus:ring-cyan">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- TAB 6: SUBSCRIPTION PLANS (Visual Plan Manager - NO RAW JSON) -->
                <div x-show="activeTab === 'subscriptions'" x-cloak class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden" id="section-subscriptions">
                    <div class="border-b border-gray-100 px-6 py-4 bg-gray-50/50 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                        <div class="flex items-center space-x-3">
                            <span class="flex items-center justify-center w-7 h-7 rounded-md bg-navy text-white text-xs font-black">06</span>
                            <div>
                                <h3 class="text-sm font-bold text-navy uppercase tracking-wide">Subscription Plans &amp; Pricing</h3>
                                <p class="text-xs text-gray-500">Visual Plan Manager with chip features. Fully backward-compatible with CartController.</p>
                            </div>
                        </div>
                        <button type="button" @click="openPlanModal()" class="inline-flex items-center gap-1.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs uppercase px-4 py-2 rounded-lg shadow transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                            <span>Add New Plan</span>
                        </button>
                    </div>

                    <!-- Hidden JSON field that syncs with form submit -->
                    <input type="hidden" name="subscription_plans" :value="JSON.stringify(plans)">

                    <div class="p-6 space-y-6">
                        <!-- Plans Cards Grid -->
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                            <template x-for="(plan, index) in plans" :key="index">
                                <div class="border border-gray-200 rounded-xl p-5 bg-gray-50/50 hover:bg-white hover:border-cyan/40 hover:shadow-md transition-all flex flex-col justify-between space-y-4">
                                    <div>
                                        <div class="flex items-center justify-between mb-2">
                                            <h4 class="text-base font-black text-navy uppercase font-heading" x-text="plan.name"></h4>
                                            <span class="px-2 py-0.5 text-[10px] font-extrabold uppercase rounded-full"
                                                  :class="plan.status === 'disabled' ? 'bg-gray-200 text-gray-600' : 'bg-emerald-100 text-emerald-800'"
                                                  x-text="plan.status === 'disabled' ? 'Disabled' : 'Active'"></span>
                                        </div>

                                        <div class="flex items-baseline gap-2 mb-4">
                                            <div class="text-2xl font-black text-navy font-mono">
                                                $<span x-text="plan.price_monthly"></span><span class="text-xs text-gray-400 font-sans font-normal">/mo</span>
                                            </div>
                                            <span class="text-gray-300">|</span>
                                            <div class="text-xs font-bold text-gray-500 font-mono">
                                                $<span x-text="plan.price_annual"></span>/yr
                                            </div>
                                        </div>

                                        <!-- Features List -->
                                        <div class="space-y-1.5 border-t border-gray-200 pt-3">
                                            <template x-for="(feat, fIndex) in (plan.features || [])" :key="fIndex">
                                                <div class="flex items-center gap-1.5 text-xs text-gray-600">
                                                    <svg class="w-3.5 h-3.5 text-emerald-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                                                    </svg>
                                                    <span class="truncate" x-text="feat"></span>
                                                </div>
                                            </template>
                                        </div>
                                    </div>

                                    <!-- Plan Actions -->
                                    <div class="flex items-center justify-between pt-3 border-t border-gray-200">
                                        <div class="flex items-center gap-1">
                                            <button type="button" @click="editPlan(index)" class="p-1.5 text-gray-500 hover:text-navy hover:bg-gray-100 rounded transition" title="Edit Plan">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                            </button>
                                            <button type="button" @click="duplicatePlan(index)" class="p-1.5 text-gray-500 hover:text-cyan hover:bg-gray-100 rounded transition" title="Duplicate Plan">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" /></svg>
                                            </button>
                                            <button type="button" @click="togglePlanStatus(index)" class="p-1.5 text-gray-500 hover:text-orange hover:bg-gray-100 rounded transition" :title="plan.status === 'disabled' ? 'Enable' : 'Disable'">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" /></svg>
                                            </button>
                                        </div>
                                        <button type="button" @click="deletePlan(index)" class="p-1.5 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded transition" title="Delete Plan">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                        </button>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>

                <!-- TAB 7: EMAIL SETTINGS -->
                <div x-show="activeTab === 'email'" x-cloak class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden" id="section-email">
                    <div class="border-b border-gray-100 px-6 py-4 bg-gray-50/50 flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <span class="flex items-center justify-center w-7 h-7 rounded-md bg-navy text-white text-xs font-black">07</span>
                            <div>
                                <h3 class="text-sm font-bold text-navy uppercase tracking-wide">Transactional Email Settings</h3>
                                <p class="text-xs text-gray-500">Configure outbound sender identity and administrative alerts. SMTP secrets remain secured in environment.</p>
                            </div>
                        </div>
                    </div>

                    <div class="p-6 space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="mail_from_name" class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-1.5">
                                    Mail From Name
                                </label>
                                <input type="text" name="mail_from_name" id="mail_from_name" @input="markDirty()" value="{{ old('mail_from_name', $settings['mail_from_name'] ?? config('mail.from.name', 'Exam Topics Base')) }}" class="w-full text-sm border-gray-300 rounded-lg focus:border-cyan focus:ring-cyan">
                            </div>

                            <div>
                                <label for="mail_from_address" class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-1.5">
                                    Mail From Address
                                </label>
                                <input type="email" name="mail_from_address" id="mail_from_address" @input="markDirty()" value="{{ old('mail_from_address', $settings['mail_from_address'] ?? config('mail.from.address', 'noreply@examtopicsbase.com')) }}" class="w-full text-sm border-gray-300 rounded-lg focus:border-cyan focus:ring-cyan">
                            </div>

                            <div>
                                <label for="order_notification_email" class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-1.5">
                                    Order Notification Recipient Email
                                </label>
                                <input type="email" name="order_notification_email" id="order_notification_email" @input="markDirty()" value="{{ old('order_notification_email', $settings['order_notification_email'] ?? 'orders@examtopicsbase.com') }}" class="w-full text-sm border-gray-300 rounded-lg focus:border-cyan focus:ring-cyan">
                            </div>

                            <div>
                                <label for="admin_notification_email" class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-1.5">
                                    System Alerts &amp; Admin Email
                                </label>
                                <input type="email" name="admin_notification_email" id="admin_notification_email" @input="markDirty()" value="{{ old('admin_notification_email', $settings['admin_notification_email'] ?? 'admin@examtopicsbase.com') }}" class="w-full text-sm border-gray-300 rounded-lg focus:border-cyan focus:ring-cyan">
                            </div>
                        </div>

                        <div class="bg-gray-50 border border-gray-200 rounded-xl p-4 text-xs text-gray-600 flex items-start gap-3">
                            <svg class="w-5 h-5 text-gray-400 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            <div>
                                <span class="font-bold text-navy">Security Notice:</span> SMTP server credentials, Mailgun/Postmark/Resend API tokens, and secret passwords are managed via backend environment variables (<span class="font-mono text-navy font-bold">.env</span>) and are never exposed in administrative forms.
                            </div>
                        </div>
                    </div>
                </div>

                <!-- TAB 8: PAYMENTS (Read-only status & public keys) -->
                <div x-show="activeTab === 'payments'" x-cloak class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden" id="section-payments">
                    <div class="border-b border-gray-100 px-6 py-4 bg-gray-50/50 flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <span class="flex items-center justify-center w-7 h-7 rounded-md bg-navy text-white text-xs font-black">08</span>
                            <div>
                                <h3 class="text-sm font-bold text-navy uppercase tracking-wide">Payment Gateways &amp; Webhooks</h3>
                                <p class="text-xs text-gray-500">Live operational status for Stripe &amp; PayPal. Secrets are securely preserved.</p>
                            </div>
                        </div>
                    </div>

                    <div class="p-6 space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Stripe Gateway Status Card -->
                            <div class="border border-gray-200 rounded-xl p-5 bg-gray-50/50 space-y-4">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-8 h-8 rounded-lg bg-[#635BFF] text-white flex items-center justify-center font-bold text-xs">
                                            S
                                        </div>
                                        <div>
                                            <h4 class="text-sm font-black text-navy">Stripe Payments</h4>
                                            <span class="text-[11px] text-gray-400">Credit Cards, Apple Pay, Google Pay</span>
                                        </div>
                                    </div>
                                    <span class="px-2.5 py-1 text-xs font-bold rounded-full {{ $paymentInfo['stripe_configured'] ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                                        {{ $paymentInfo['stripe_configured'] ? 'Active' : 'Test / Mock Mode' }}
                                    </span>
                                </div>

                                <div class="space-y-2 text-xs border-t border-gray-200 pt-3">
                                    <div class="flex justify-between">
                                        <span class="text-gray-500">Mode:</span>
                                        <span class="font-bold text-navy">{{ $paymentInfo['stripe_mode'] }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-500">Public Key:</span>
                                        <span class="font-mono text-gray-700">{{ $paymentInfo['stripe_public_key'] }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-500">Webhook Status:</span>
                                        <span class="font-bold {{ $paymentInfo['stripe_webhook_configured'] ? 'text-emerald-700' : 'text-amber-700' }}">
                                            {{ $paymentInfo['stripe_webhook_configured'] ? 'Configured (/webhook/stripe)' : 'Webhook Secret Pending' }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <!-- PayPal Gateway Status Card -->
                            <div class="border border-gray-200 rounded-xl p-5 bg-gray-50/50 space-y-4">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-8 h-8 rounded-lg bg-[#003087] text-white flex items-center justify-center font-bold text-xs">
                                            P
                                        </div>
                                        <div>
                                            <h4 class="text-sm font-black text-navy">PayPal Standard</h4>
                                            <span class="text-[11px] text-gray-400">PayPal Express &amp; Subscriptions</span>
                                        </div>
                                    </div>
                                    <span class="px-2.5 py-1 text-xs font-bold rounded-full {{ $paymentInfo['paypal_configured'] ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                                        {{ $paymentInfo['paypal_configured'] ? 'Active' : 'Sandbox Mode' }}
                                    </span>
                                </div>

                                <div class="space-y-2 text-xs border-t border-gray-200 pt-3">
                                    <div class="flex justify-between">
                                        <span class="text-gray-500">Mode:</span>
                                        <span class="font-bold text-navy">{{ $paymentInfo['paypal_mode'] }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-500">Webhook URL:</span>
                                        <span class="font-mono text-gray-700 text-[11px]">/webhook/paypal</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- TAB 9: MAINTENANCE MODE -->
                <div x-show="activeTab === 'maintenance'" x-cloak class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden" id="section-maintenance">
                    <div class="border-b border-gray-100 px-6 py-4 bg-gray-50/50 flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <span class="flex items-center justify-center w-7 h-7 rounded-md bg-navy text-white text-xs font-black">09</span>
                            <div>
                                <h3 class="text-sm font-bold text-navy uppercase tracking-wide">Maintenance Mode</h3>
                                <p class="text-xs text-gray-500">Emergency website offline toggle. Authenticated administrators automatically bypass offline screen.</p>
                            </div>
                        </div>
                    </div>

                    <div class="p-6 space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="maintenance_mode" class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-1.5">
                                    Maintenance Status
                                </label>
                                <select name="maintenance_mode" id="maintenance_mode" @change="markDirty()" class="w-full text-sm border-gray-300 rounded-lg focus:border-cyan focus:ring-cyan font-bold">
                                    <option value="false" {{ old('maintenance_mode', $settings['maintenance_mode'] ?? 'false') === 'false' ? 'selected' : '' }}>Disabled (Site Online for all visitors)</option>
                                    <option value="true" {{ old('maintenance_mode', $settings['maintenance_mode'] ?? 'false') === 'true' ? 'selected' : '' }}>Enabled (Site Offline / Maintenance Notice)</option>
                                </select>
                            </div>

                            <div>
                                <label for="maintenance_return_time" class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-1.5">
                                    Expected Return Notice (Optional)
                                </label>
                                <input type="text" name="maintenance_return_time" id="maintenance_return_time" @input="markDirty()" value="{{ old('maintenance_return_time', $settings['maintenance_return_time'] ?? '') }}" placeholder="e.g. Back online today at 4:00 PM EST" class="w-full text-sm border-gray-300 rounded-lg focus:border-cyan focus:ring-cyan">
                            </div>
                        </div>

                        <div>
                            <label for="maintenance_message" class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-1.5">
                                Public Maintenance Message
                            </label>
                            <textarea name="maintenance_message" id="maintenance_message" @input="markDirty()" rows="3" placeholder="We are performing brief scheduled system maintenance. All user progress is secure." class="w-full text-sm border-gray-300 rounded-lg focus:border-cyan focus:ring-cyan">{{ old('maintenance_message', $settings['maintenance_message'] ?? 'We are performing brief scheduled system maintenance to improve our practice engine. All purchased guides are secure. We will be back online shortly!') }}</textarea>
                        </div>
                    </div>
                </div>

                <!-- TAB 10: SECURITY -->
                <div x-show="activeTab === 'security'" x-cloak class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden" id="section-security">
                    <div class="border-b border-gray-100 px-6 py-4 bg-gray-50/50 flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <span class="flex items-center justify-center w-7 h-7 rounded-md bg-navy text-white text-xs font-black">10</span>
                            <div>
                                <h3 class="text-sm font-bold text-navy uppercase tracking-wide">Security &amp; Session Management</h3>
                                <p class="text-xs text-gray-500">Control session timeout limits, brute-force rate limits, and password criteria.</p>
                            </div>
                        </div>
                    </div>

                    <div class="p-6 space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div>
                                <label for="session_timeout_minutes" class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-1.5">
                                    Session Lifetime (Minutes)
                                </label>
                                <input type="number" name="session_timeout_minutes" id="session_timeout_minutes" @input="markDirty()" value="{{ old('session_timeout_minutes', $settings['session_timeout_minutes'] ?? '120') }}" min="15" max="1440" class="w-full text-sm border-gray-300 rounded-lg focus:border-cyan focus:ring-cyan font-mono">
                            </div>

                            <div>
                                <label for="max_login_attempts" class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-1.5">
                                    Max Failed Login Attempts
                                </label>
                                <input type="number" name="max_login_attempts" id="max_login_attempts" @input="markDirty()" value="{{ old('max_login_attempts', $settings['max_login_attempts'] ?? '5') }}" min="3" max="20" class="w-full text-sm border-gray-300 rounded-lg focus:border-cyan focus:ring-cyan font-mono">
                            </div>

                            <div>
                                <label for="password_min_length" class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-1.5">
                                    Minimum Password Length
                                </label>
                                <input type="number" name="password_min_length" id="password_min_length" @input="markDirty()" value="{{ old('password_min_length', $settings['password_min_length'] ?? '8') }}" min="8" max="32" class="w-full text-sm border-gray-300 rounded-lg focus:border-cyan focus:ring-cyan font-mono">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Bottom Sticky Floating Save Bar -->
                <div class="flex items-center justify-between pt-4 border-t border-gray-200">
                    <span class="text-xs text-gray-400">ExamTopicsBase Core Engine v2.0</span>
                    <button type="submit" :disabled="isSubmitting" class="bg-navy hover:bg-navy/90 text-white font-bold text-xs uppercase tracking-wider px-7 py-3 rounded-lg shadow-md transition disabled:opacity-50 flex items-center gap-2">
                        <svg class="w-4 h-4 text-cyan" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                        <span x-text="isSubmitting ? 'Saving...' : 'Save Settings'">Save Settings</span>
                    </button>
                </div>
            </form>

            <!-- TAB 11: ADVANCED & SYSTEM CACHE TOOLS (Separate standalone form for cache clears) -->
            <div x-show="activeTab === 'advanced'" x-cloak class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden" id="section-advanced">
                <div class="border-b border-gray-100 px-6 py-4 bg-gray-50/50 flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <span class="flex items-center justify-center w-7 h-7 rounded-md bg-navy text-white text-xs font-black">11</span>
                        <div>
                            <h3 class="text-sm font-bold text-navy uppercase tracking-wide">System &amp; Cache Maintenance</h3>
                            <p class="text-xs text-gray-500">Safely clear application and view caches when changes are made.</p>
                        </div>
                    </div>
                </div>

                <div class="p-6 space-y-6">
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                        <!-- Application Cache -->
                        <div class="border border-gray-200 rounded-xl p-4 bg-gray-50/50 flex flex-col justify-between space-y-3">
                            <div>
                                <h4 class="text-xs font-bold text-navy uppercase">App Data Cache</h4>
                                <p class="text-[11px] text-gray-500 mt-1">Clears application query caches, statistics caches, and keys.</p>
                            </div>
                            <form action="{{ route('admin.settings.clear-cache') }}" method="POST" onsubmit="return confirm('Clear application cache?')">
                                @csrf
                                <input type="hidden" name="cache_type" value="application">
                                <button type="submit" class="w-full text-xs font-bold text-navy bg-white border border-gray-300 hover:bg-gray-50 py-1.5 px-3 rounded-lg transition shadow-sm">
                                    Clear App Cache
                                </button>
                            </form>
                        </div>

                        <!-- View Cache -->
                        <div class="border border-gray-200 rounded-xl p-4 bg-gray-50/50 flex flex-col justify-between space-y-3">
                            <div>
                                <h4 class="text-xs font-bold text-navy uppercase">Blade Views Cache</h4>
                                <p class="text-[11px] text-gray-500 mt-1">Purges all compiled Blade template views from storage.</p>
                            </div>
                            <form action="{{ route('admin.settings.clear-cache') }}" method="POST" onsubmit="return confirm('Clear compiled Blade view templates?')">
                                @csrf
                                <input type="hidden" name="cache_type" value="view">
                                <button type="submit" class="w-full text-xs font-bold text-navy bg-white border border-gray-300 hover:bg-gray-50 py-1.5 px-3 rounded-lg transition shadow-sm">
                                    Clear Views Cache
                                </button>
                            </form>
                        </div>

                        <!-- Route Cache -->
                        <div class="border border-gray-200 rounded-xl p-4 bg-gray-50/50 flex flex-col justify-between space-y-3">
                            <div>
                                <h4 class="text-xs font-bold text-navy uppercase">Routes Cache</h4>
                                <p class="text-[11px] text-gray-500 mt-1">Clears registered route mappings and controller lookups.</p>
                            </div>
                            <form action="{{ route('admin.settings.clear-cache') }}" method="POST" onsubmit="return confirm('Clear route cache?')">
                                @csrf
                                <input type="hidden" name="cache_type" value="route">
                                <button type="submit" class="w-full text-xs font-bold text-navy bg-white border border-gray-300 hover:bg-gray-50 py-1.5 px-3 rounded-lg transition shadow-sm">
                                    Clear Route Cache
                                </button>
                            </form>
                        </div>

                        <!-- Flush All -->
                        <div class="border border-orange/30 rounded-xl p-4 bg-orange/5 flex flex-col justify-between space-y-3">
                            <div>
                                <h4 class="text-xs font-bold text-orange uppercase">Flush All Caches</h4>
                                <p class="text-[11px] text-gray-600 mt-1">Safely flushes application, views, routes, and config.</p>
                            </div>
                            <form action="{{ route('admin.settings.clear-cache') }}" method="POST" onsubmit="return confirm('Are you sure you want to flush all system and template caches?')">
                                @csrf
                                <input type="hidden" name="cache_type" value="all">
                                <button type="submit" class="w-full text-xs font-bold text-white bg-orange hover:bg-orange/90 py-1.5 px-3 rounded-lg transition shadow-sm">
                                    Flush All Caches
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- PLAN EDITOR MODAL (Interactive Drawer) -->
    <div x-show="planModalOpen" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
            <!-- Background Backdrop -->
            <div x-show="planModalOpen" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                 @click="planModalOpen = false" class="fixed inset-0 bg-navy/60 backdrop-blur-xs transition-opacity"></div>

            <!-- Modal Dialog Content -->
            <div x-show="planModalOpen" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 class="relative inline-block w-full max-w-lg p-6 my-8 overflow-hidden text-left align-middle transition-all transform bg-white shadow-2xl rounded-2xl border border-gray-200">
                
                <div class="flex items-center justify-between pb-4 border-b border-gray-100">
                    <h3 class="text-base font-black text-navy uppercase font-heading" x-text="editingPlanIndex !== null ? 'Edit Subscription Plan' : 'Create New Subscription Plan'"></h3>
                    <button type="button" @click="planModalOpen = false" class="text-gray-400 hover:text-gray-600 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>

                <div class="space-y-4 pt-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-1">Plan Name <span class="text-red-500">*</span></label>
                        <input type="text" x-model="currentPlan.name" placeholder="e.g. Pro, Ultimate, Team" class="w-full text-sm border-gray-300 rounded-lg focus:border-cyan focus:ring-cyan font-bold">
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-1">Monthly Price ($)</label>
                            <input type="number" step="0.01" min="0" x-model.number="currentPlan.price_monthly" class="w-full text-sm border-gray-300 rounded-lg focus:border-cyan focus:ring-cyan font-mono font-bold">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-1">Annual Price ($)</label>
                            <input type="number" step="0.01" min="0" x-model.number="currentPlan.price_annual" class="w-full text-sm border-gray-300 rounded-lg focus:border-cyan focus:ring-cyan font-mono font-bold">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-1">Plan Features</label>
                        <div class="flex gap-2 mb-2">
                            <input type="text" x-model="newFeatureText" @keydown.enter.prevent="addFeature()" placeholder="Type a feature and press Enter or Add..." class="w-full text-xs border-gray-300 rounded-lg focus:border-cyan focus:ring-cyan">
                            <button type="button" @click="addFeature()" class="px-3 py-1.5 bg-navy text-white text-xs font-bold rounded-lg hover:bg-navy/90 transition">Add</button>
                        </div>

                        <!-- Feature Chips -->
                        <div class="flex flex-wrap gap-1.5 p-2 bg-gray-50 border border-gray-200 rounded-lg min-h-[50px]">
                            <template x-for="(feat, fIdx) in currentPlan.features" :key="fIdx">
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium bg-white text-navy border border-gray-200 shadow-2xs">
                                    <span x-text="feat"></span>
                                    <button type="button" @click="removeFeature(fIdx)" class="text-gray-400 hover:text-red-500 font-bold ml-1">&times;</button>
                                </span>
                            </template>
                            <span x-show="!currentPlan.features.length" class="text-xs text-gray-400 italic">No features added yet.</span>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-1">Plan Status</label>
                        <select x-model="currentPlan.status" class="w-full text-xs border-gray-300 rounded-lg focus:border-cyan focus:ring-cyan">
                            <option value="active">Active (Available for customer subscription)</option>
                            <option value="disabled">Disabled (Hidden from public checkout)</option>
                        </select>
                    </div>
                </div>

                <div class="mt-6 pt-4 border-t border-gray-100 flex items-center justify-end gap-3">
                    <button type="button" @click="planModalOpen = false" class="px-4 py-2 text-xs font-bold text-gray-600 hover:bg-gray-100 rounded-lg transition">
                        Cancel
                    </button>
                    <button type="button" @click="savePlan()" class="px-5 py-2 text-xs font-bold text-white bg-navy hover:bg-navy/90 rounded-lg shadow transition">
                        Save Plan
                    </button>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection
