@extends('layouts.admin')

@section('title', 'Create New Certification Exam')

@section('content')
@php
$certData = $certifications->map(function($c) {
    return [
        'id' => $c->id,
        'name' => $c->name,
        'vendor_name' => $c->vendor ? $c->vendor->name : 'Unknown',
        'vendor_id' => $c->vendor_id
    ];
})->values()->toJson();

$preSelected = json_encode(old('certifications', []));
$oldTopics = old('topics', []);
$oldTopicsList = is_array($oldTopics) ? array_values(array_filter(array_map('trim', $oldTopics))) : [];

$workspaceConfig = [
    'examCode' => (string)old('exam_code', ''),
    'examName' => (string)old('exam_name', ''),
    'headerTitle' => (string)old('header_title', ''),
    'vendorId' => (string)old('vendor_id', ''),
    'vendorName' => '',
    'difficulty' => (string)old('difficulty', 'Associate'),
    'examType' => (string)old('exam_type', 'MultipleChoice'),
    'passingScore' => (int)old('passing_score', 70),
    'questionCount' => (string)old('question_count', ''),
    'actualQuestions' => 0,
    'isPdfAvailable' => (bool)old('is_pdf_available', '1'),
    'isEngineAvailable' => (bool)old('is_engine_available', '1'),
    'isBundleAvailable' => (bool)old('is_bundle_available', '1'),
    'pricePdf' => (string)old('price_pdf', '29.00'),
    'priceEngine' => (string)old('price_engine', '39.00'),
    'priceBundle' => (string)old('price_bundle', '59.00'),
    'update3' => (string)old('update_price_3_months', '0.00'),
    'update6' => (string)old('update_price_6_months', '10.00'),
    'update12' => (string)old('update_price_12_months', '20.00'),
    'isActive' => (bool)old('is_active', '1'),
    'isFeatured' => (bool)old('is_featured', 0),
    'slug' => (string)old('slug', ''),
    'sortOrder' => (int)old('sort_order', 0),
    'metaTitle' => (string)old('meta_title', ''),
    'metaDescription' => (string)old('meta_description', ''),
    'metaKeywords' => (string)old('meta_keywords', ''),
    'hasDemoPdf' => false,
    'hasFullPdf' => false,
    'topics' => $oldTopicsList,
];
@endphp

<div x-data="examWorkspace({{ json_encode($workspaceConfig) }})" class="space-y-8 pb-20">

    <!-- Top Workspace Header -->
    <div class="bg-white border border-gray-200 rounded-xl p-6 shadow-sm">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
            <!-- Left: Breadcrumb & Title -->
            <div class="space-y-2">
                <div class="flex items-center space-x-2 text-xs font-semibold uppercase tracking-wider text-gray-500">
                    <a href="{{ route('admin.dashboard') }}" class="hover:text-navy transition">Dashboard</a>
                    <span>/</span>
                    <a href="{{ route('admin.exams.index') }}" class="hover:text-navy transition">Exams</a>
                    <span>/</span>
                    <span class="text-navy font-bold">Create Exam</span>
                </div>
                <div class="flex flex-wrap items-center gap-3">
                    <h1 class="text-2xl sm:text-3xl font-black text-navy tracking-tight font-heading">
                        Create New Exam
                    </h1>
                    <span class="text-gray-300 text-xl font-light">|</span>
                    <span class="text-lg text-gray-500 font-medium">
                        Add a new certification exam to your catalog
                    </span>
                    <!-- Status Badge -->
                    <template x-if="isActive">
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-1.5 animate-pulse"></span>
                            Active (Will Publish)
                        </span>
                    </template>
                    <template x-if="!isActive">
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-800 border border-amber-200">
                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500 mr-1.5"></span>
                            Draft (Hidden)
                        </span>
                    </template>
                </div>
                <p class="text-xs text-gray-500">
                    Configure official certification details, commercial pricing, syllabus topics, study guide files, and SEO metadata.
                </p>
            </div>

            <!-- Right: Actions -->
            <div class="flex items-center space-x-3 shrink-0">
                <a href="{{ route('admin.exams.index') }}" class="inline-flex items-center px-4 py-2.5 rounded-lg bg-gray-100 text-gray-700 hover:bg-gray-200 text-xs font-bold transition">
                    ← Back to Listing
                </a>
            </div>
        </div>
    </div>

    <!-- Main Workspace Two-Column Layout -->
    <form id="examForm" action="{{ route('admin.exams.store') }}" method="POST" enctype="multipart/form-data" @submit="isSubmitting = true">
        @csrf
        <input type="hidden" name="action" id="formActionInput" value="publish">

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
            
            <!-- Left Column: 70% Configuration Cards -->
            <div class="lg:col-span-8 space-y-8">

                <!-- 01: BASIC INFORMATION -->
                <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden" id="section-basic">
                    <div class="border-b border-gray-100 px-6 py-4 bg-gray-50/50 flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <span class="flex items-center justify-center w-7 h-7 rounded-md bg-navy text-white text-xs font-black">01</span>
                            <div>
                                <h3 class="text-sm font-bold text-navy uppercase tracking-wide">Basic Information</h3>
                                <p class="text-xs text-gray-500">Core identification and naming for the certification catalog.</p>
                            </div>
                        </div>
                    </div>

                    <div class="p-6 space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Certification Vendor -->
                            <div>
                                <label for="vendor_id" class="block text-xs font-bold text-gray-700 uppercase mb-2">
                                    Certification Vendor <span class="text-red-500">*</span>
                                </label>
                                <select name="vendor_id" id="vendor_id" required x-model="vendorId" @change="onVendorChange($event)"
                                        class="w-full border-gray-300 rounded-lg text-sm px-3.5 py-2.5 focus:border-cyan focus:ring-cyan shadow-sm bg-white font-medium">
                                    <option value="">Select a vendor...</option>
                                    @foreach($vendors as $vendor)
                                        <option value="{{ $vendor->id }}" data-name="{{ $vendor->name }}" {{ old('vendor_id') == $vendor->id ? 'selected' : '' }}>
                                            {{ $vendor->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('vendor_id') <p class="text-red-500 text-xs mt-1.5 font-medium">{{ $message }}</p> @enderror
                            </div>

                            <!-- Exam Code -->
                            <div>
                                <label for="exam_code" class="block text-xs font-bold text-gray-700 uppercase mb-2">
                                    Exam Code <span class="text-red-500">*</span>
                                </label>
                                <div class="relative rounded-lg shadow-sm">
                                    <input type="text" name="exam_code" id="exam_code" required x-model="examCode" @input="markDirty()"
                                           placeholder="e.g. 200-301 or AZ-900"
                                           class="w-full border-gray-300 rounded-lg text-sm px-3.5 py-2.5 uppercase font-mono font-bold text-navy focus:border-cyan focus:ring-cyan">
                                </div>
                                <p class="text-[11px] text-gray-400 mt-1">Official vendor exam code identifier.</p>
                                @error('exam_code') <p class="text-red-500 text-xs mt-1.5 font-medium">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <!-- Exam Name -->
                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <label for="exam_name" class="block text-xs font-bold text-gray-700 uppercase">
                                    Official Exam Name <span class="text-red-500">*</span>
                                </label>
                                <span class="text-[11px] text-gray-400 font-mono" x-text="(examName ? examName.length : 0) + ' characters'"></span>
                            </div>
                            <input type="text" name="exam_name" id="exam_name" required x-model="examName" @input="markDirty()"
                                   placeholder="e.g. Cisco Certified Network Associate (CCNA)"
                                   class="w-full border-gray-300 rounded-lg text-sm px-3.5 py-2.5 text-gray-800 font-semibold focus:border-cyan focus:ring-cyan shadow-sm">
                            <p class="text-[11px] text-gray-400 mt-1">Full canonical certification title displayed across invoices, catalog, and breadcrumbs.</p>
                            @error('exam_name') <p class="text-red-500 text-xs mt-1.5 font-medium">{{ $message }}</p> @enderror
                        </div>

                        <!-- Header Title (H1 Display Heading) -->
                        <div class="bg-cyan/5 border border-cyan/20 rounded-lg p-4">
                            <div class="flex items-center justify-between mb-1.5">
                                <label for="header_title" class="block text-xs font-bold text-navy uppercase flex items-center gap-1.5">
                                    <svg class="w-4 h-4 text-cyan" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" /></svg>
                                    Custom Header Title (H1 Hero Area)
                                </label>
                                <span class="text-[10px] font-bold uppercase tracking-wider text-cyan bg-cyan/10 px-2 py-0.5 rounded">Optional</span>
                            </div>
                            <input type="text" name="header_title" id="header_title" x-model="headerTitle" @input="markDirty()"
                                   value="{{ old('header_title', '') }}"
                                   placeholder="e.g. Cisco CCNA 200-301 Certification"
                                   class="w-full border-gray-300 rounded-lg text-sm px-3.5 py-2.5 text-navy font-medium focus:border-cyan focus:ring-cyan shadow-sm bg-white">
                            <p class="text-[11px] text-gray-600 mt-1.5">
                                <strong>Tip:</strong> Replaces the long Exam Name in the top H1 hero heading on the public exam page. If left blank, it automatically defaults to <em>Vendor + Exam Code</em> (e.g. <code>Cisco 200-301</code>).
                            </p>
                            @error('header_title') <p class="text-red-500 text-xs mt-1.5 font-medium">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>

                <!-- 02: CLASSIFICATION & CERTIFICATION -->
                <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden" id="section-classification">
                    <div class="border-b border-gray-100 px-6 py-4 bg-gray-50/50 flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <span class="flex items-center justify-center w-7 h-7 rounded-md bg-navy text-white text-xs font-black">02</span>
                            <div>
                                <h3 class="text-sm font-bold text-navy uppercase tracking-wide">Certification & Classification</h3>
                                <p class="text-xs text-gray-500">Associate with certification paths and set exam testing specs.</p>
                            </div>
                        </div>
                    </div>

                    <div class="p-6 space-y-6">
                        <!-- Custom Certifications Multi-Select & Standalone Modal (AlpineJS) -->
                        <div x-data="certDropdown({{ $certData }}, {{ $preSelected }})" class="relative">
                            <!-- Toast Notification -->
                            <div x-show="toastMessage" x-transition.duration.300ms style="display: none;" class="fixed top-6 right-6 z-50 flex items-center gap-2.5 bg-navy text-white text-xs font-bold px-5 py-3.5 rounded-xl shadow-2xl border border-cyan/40">
                                <span class="w-2.5 h-2.5 rounded-full bg-cyan animate-pulse"></span>
                                <span x-text="toastMessage"></span>
                            </div>

                            <div class="flex items-center justify-between mb-2">
                                <label class="block text-xs font-bold text-gray-700 uppercase">
                                    Linked Certifications
                                </label>
                                <span class="text-[11px] text-gray-400" x-text="selectedCerts.length + ' certifications linked'"></span>
                            </div>
                            
                            <!-- Main Input Area with "+ Create Certification" Button beside it -->
                            <div class="flex flex-col sm:flex-row items-stretch gap-2.5">
                                <div @click="openDropdown()" class="min-h-[46px] flex-grow border border-gray-300 rounded-lg text-sm p-2 focus-within:border-cyan focus-within:ring-1 focus-within:ring-cyan bg-white flex flex-wrap gap-2 items-center cursor-text transition-colors shadow-sm">
                                    <template x-for="cert in selectedCerts" :key="cert.id">
                                        <span class="bg-navy text-white text-xs font-semibold px-2.5 py-1 rounded-md flex items-center gap-1.5 shadow-sm">
                                            <span x-text="cert.name"></span>
                                            <button type="button" @click.stop="removeCert(cert.id)" class="text-gray-300 hover:text-red-400 focus:outline-none transition-colors">
                                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                            </button>
                                            <input type="hidden" name="certifications[]" :value="cert.id">
                                        </span>
                                    </template>
                                    <input type="text" x-model="search" x-ref="searchInput" @keydown.backspace="removeLastIfEmpty()" class="flex-grow border-0 focus:ring-0 text-sm p-1 min-w-[140px] outline-none placeholder-gray-400" placeholder="Search & select certifications...">
                                </div>

                                <!-- Prominent + Create Certification Button -->
                                <button type="button" @click.prevent="openCreateModal()" class="shrink-0 inline-flex items-center justify-center px-4 py-2.5 bg-navy hover:bg-gray-800 text-white text-xs font-bold rounded-lg shadow-sm transition-all border border-navy hover:border-gray-900 gap-1.5 group">
                                    <svg class="w-4 h-4 text-cyan group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                                    <span>+ Create Certification</span>
                                </button>
                            </div>

                            <!-- Dropdown Panel -->
                            <div x-show="isOpen" @click.away="isOpen = false" x-transition.opacity.duration.150ms class="absolute z-40 mt-1.5 w-full bg-white border border-gray-200 rounded-xl shadow-2xl overflow-hidden" style="display: none;">
                                <div class="flex flex-col max-h-72">
                                    <ul class="overflow-y-auto flex-grow py-1 divide-y divide-gray-50">
                                        <template x-for="cert in filteredCerts" :key="cert.id">
                                            <li @click="toggleCert(cert)" class="px-4 py-2.5 hover:bg-cyan/10 cursor-pointer flex items-center justify-between transition-colors">
                                                <div>
                                                    <div class="flex items-center gap-2">
                                                        <span x-text="cert.name" class="text-sm font-bold text-gray-800"></span>
                                                        <template x-if="cert.code">
                                                            <span x-text="cert.code" class="text-[10px] font-mono font-bold bg-gray-100 text-gray-600 px-1.5 py-0.5 rounded"></span>
                                                        </template>
                                                    </div>
                                                    <div x-text="cert.vendor_name" class="text-[10px] uppercase font-bold text-gray-400 mt-0.5"></div>
                                                </div>
                                                <div x-show="isSelected(cert.id)" class="text-cyan">
                                                    <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" /></svg>
                                                </div>
                                            </li>
                                        </template>
                                        <li x-show="filteredCerts.length === 0" class="px-4 py-6 text-sm text-gray-500 text-center flex flex-col items-center">
                                            <span>No certifications found for "<span x-text="search" class="font-bold"></span>".</span>
                                        </li>
                                    </ul>
                                    <div class="border-t border-gray-150 p-2 bg-gray-50">
                                        <button type="button" @click.stop="openCreateModal(search)" class="w-full text-left text-xs font-bold text-cyan hover:text-navy px-3 py-2 flex items-center transition-colors rounded hover:bg-white">
                                            <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                                            <span x-text="search ? '+ Create &quot;' + search + '&quot;' : '+ Create Certification'"></span>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Professional Standalone Modal Dialog -->
                            <div x-show="isModalOpen" style="display: none;" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
                                <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                                    <!-- Backdrop -->
                                    <div x-show="isModalOpen" x-transition.opacity.duration.200ms @click="closeCreateModal()" class="fixed inset-0 bg-navy/60 backdrop-blur-sm transition-opacity"></div>
                                    <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                                    <!-- Modal Card Panel -->
                                    <div x-show="isModalOpen" x-transition.scale.duration.200ms class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg w-full border border-gray-200">
                                        <!-- Modal Header -->
                                        <div class="bg-navy px-6 py-4 flex items-center justify-between">
                                            <div class="flex items-center space-x-2.5">
                                                <span class="w-2.5 h-2.5 rounded-full bg-cyan animate-pulse"></span>
                                                <h3 class="text-base font-bold text-white tracking-wide" id="modal-title">Create New Certification</h3>
                                            </div>
                                            <button type="button" @click="closeCreateModal()" class="text-gray-400 hover:text-white transition-colors focus:outline-none">
                                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                            </button>
                                        </div>

                                        <!-- Modal Body -->
                                        <div class="p-6 space-y-4">
                                            <!-- Inline Error Alert -->
                                            <div x-show="errorMessage && !duplicateCert" x-text="errorMessage" class="bg-red-50 text-red-600 border border-red-200 p-3 rounded-lg text-xs font-bold"></div>

                                            <!-- Duplicate Alert with [Use Existing Certification] Button -->
                                            <div x-show="duplicateCert" class="bg-amber-50 border border-amber-200 rounded-xl p-4 space-y-2.5">
                                                <div class="flex items-start gap-2.5">
                                                    <svg class="w-5 h-5 text-amber-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                                    <div class="text-xs text-amber-900">
                                                        <strong class="font-bold">Certification already exists:</strong>
                                                        <div class="mt-0.5 font-medium" x-text="duplicateCert ? (duplicateCert.name + (duplicateCert.vendor_name ? ' (' + duplicateCert.vendor_name + ')' : '')) : ''"></div>
                                                    </div>
                                                </div>
                                                <button type="button" @click="useExistingCert(duplicateCert)" class="w-full bg-amber-600 hover:bg-amber-700 text-white font-bold text-xs py-2 px-3 rounded-lg transition-colors flex items-center justify-center gap-1.5 shadow-sm">
                                                    <span>Use Existing Certification</span>
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                                </button>
                                            </div>

                                            <!-- Fields -->
                                            <div>
                                                <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Certification Vendor <span class="text-red-500">*</span></label>
                                                <select x-model="newCert.vendor_id" class="w-full border-gray-300 rounded-lg text-sm p-2.5 focus:ring-cyan focus:border-cyan shadow-sm bg-white font-medium">
                                                    <option value="">Select Vendor...</option>
                                                    @foreach($vendors as $vendor)
                                                        <option value="{{ $vendor->id }}">{{ $vendor->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                                <div>
                                                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Certification Name <span class="text-red-500">*</span></label>
                                                    <input type="text" x-model="newCert.name" class="w-full border-gray-300 rounded-lg text-sm p-2.5 focus:ring-cyan focus:border-cyan shadow-sm font-medium" placeholder="e.g. CCNA Routing and Switching">
                                                </div>
                                                <div>
                                                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Certification Code <span class="text-gray-400 font-normal">(Optional)</span></label>
                                                    <input type="text" x-model="newCert.code" class="w-full border-gray-300 rounded-lg text-sm p-2.5 font-mono uppercase focus:ring-cyan focus:border-cyan shadow-sm" placeholder="e.g. 200-301">
                                                </div>
                                            </div>

                                            <div>
                                                <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Description <span class="text-gray-400 font-normal">(Optional)</span></label>
                                                <textarea x-model="newCert.description" rows="2" class="w-full border-gray-300 rounded-lg text-sm p-2.5 focus:ring-cyan focus:border-cyan shadow-sm" placeholder="Brief certification details or curriculum outline..."></textarea>
                                            </div>

                                            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg border border-gray-200">
                                                <div>
                                                    <span class="block text-xs font-bold text-gray-700 uppercase">Certification Status</span>
                                                    <span class="text-[11px] text-gray-500">Active certifications are visible in catalog</span>
                                                </div>
                                                <label class="relative inline-flex items-center cursor-pointer">
                                                    <input type="checkbox" x-model="newCert.is_active" class="sr-only peer">
                                                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-500"></div>
                                                </label>
                                            </div>
                                        </div>

                                        <!-- Modal Footer -->
                                        <div class="bg-gray-50 px-6 py-3.5 border-t border-gray-200 flex items-center justify-end space-x-3">
                                            <button type="button" @click="closeCreateModal()" class="px-4 py-2 bg-white border border-gray-300 text-gray-700 font-bold rounded-lg text-xs hover:bg-gray-50 transition shadow-sm">
                                                Cancel
                                            </button>
                                            <button type="button" @click="saveNewCert()" :disabled="isSaving" class="px-5 py-2 bg-navy hover:bg-gray-800 text-white font-bold rounded-lg text-xs shadow-md transition flex items-center gap-1.5 disabled:opacity-50">
                                                <span x-show="isSaving">Saving...</span>
                                                <span x-show="!isSaving">Create Certification</span>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Classification Specs Grid -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-6 pt-2">
                            <!-- Difficulty -->
                            <div>
                                <label for="difficulty" class="block text-xs font-bold text-gray-700 uppercase mb-2">
                                    Difficulty <span class="text-red-500">*</span>
                                </label>
                                <select name="difficulty" id="difficulty" required x-model="difficulty" @change="markDirty()"
                                        class="w-full border-gray-300 rounded-lg text-sm px-3 py-2.5 focus:border-cyan focus:ring-cyan bg-white shadow-sm font-medium">
                                    @foreach(['Associate', 'Professional', 'Expert'] as $diff)
                                        <option value="{{ $diff }}" {{ old('difficulty') === $diff ? 'selected' : '' }}>{{ $diff }}</option>
                                    @endforeach
                                </select>
                                @error('difficulty') <p class="text-red-500 text-xs mt-1.5 font-medium">{{ $message }}</p> @enderror
                            </div>

                            <!-- Question Format -->
                            <div>
                                <label for="exam_type" class="block text-xs font-bold text-gray-700 uppercase mb-2">
                                    Question Format <span class="text-red-500">*</span>
                                </label>
                                <select name="exam_type" id="exam_type" required x-model="examType" @change="markDirty()"
                                        class="w-full border-gray-300 rounded-lg text-sm px-3 py-2.5 focus:border-cyan focus:ring-cyan bg-white shadow-sm font-medium">
                                    @foreach(['MultipleChoice' => 'Multiple Choice', 'MultiSelect' => 'Multi-Select', 'LabBased' => 'Lab-Based Studies'] as $val => $lbl)
                                        <option value="{{ $val }}" {{ old('exam_type') === $val ? 'selected' : '' }}>{{ $lbl }}</option>
                                    @endforeach
                                </select>
                                @error('exam_type') <p class="text-red-500 text-xs mt-1.5 font-medium">{{ $message }}</p> @enderror
                            </div>

                            <!-- Passing Score -->
                            <div>
                                <div class="flex items-center justify-between mb-2">
                                    <label for="passing_score" class="block text-xs font-bold text-gray-700 uppercase">
                                        Passing Score <span class="text-red-500">*</span>
                                    </label>
                                    <span class="text-xs font-bold text-cyan" x-text="passingScore + '%'"></span>
                                </div>
                                <div class="relative">
                                    <input type="number" name="passing_score" id="passing_score" required x-model.number="passingScore" @input="markDirty()" min="0" max="100"
                                           class="w-full border-gray-300 rounded-lg text-sm px-3.5 py-2.5 focus:border-cyan focus:ring-cyan shadow-sm font-mono font-bold">
                                </div>
                                @error('passing_score') <p class="text-red-500 text-xs mt-1.5 font-medium">{{ $message }}</p> @enderror
                            </div>

                            <!-- Questions Pool Override -->
                            <div>
                                <label for="question_count" class="block text-xs font-bold text-gray-700 uppercase mb-2">
                                    Pool Total
                                </label>
                                <input type="number" name="question_count" id="question_count" x-model="questionCount" @input="markDirty()" min="0" placeholder="e.g. 150"
                                       class="w-full border-gray-300 rounded-lg text-sm px-3.5 py-2.5 focus:border-cyan focus:ring-cyan shadow-sm font-mono font-medium">
                                <p class="text-[10px] text-gray-400 mt-1">
                                    Questions can be added after creating exam.
                                </p>
                                @error('question_count') <p class="text-red-500 text-xs mt-1.5 font-medium">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 03: PRICING & COMMERCIAL SETTINGS -->
                <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden" id="section-pricing">
                    <div class="border-b border-gray-100 px-6 py-4 bg-gray-50/50 flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <span class="flex items-center justify-center w-7 h-7 rounded-md bg-navy text-white text-xs font-black">03</span>
                            <div>
                                <h3 class="text-sm font-bold text-navy uppercase tracking-wide">Pricing & Commercial Settings</h3>
                                <p class="text-xs text-gray-500">Set base customer access prices and structured update subscription fees.</p>
                            </div>
                        </div>
                    </div>

                    <div class="p-6 space-y-6">
                        <input type="hidden" name="availability_configured" value="1">

                        <!-- Error Alert if no product enabled -->
                        @error('product_availability')
                            <div class="bg-red-50 border border-red-200 text-red-700 p-3 rounded-xl text-xs font-bold flex items-center gap-2">
                                <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                <span>{{ $message }}</span>
                            </div>
                        @enderror

                        <div x-show="!isPdfAvailable && !isEngineAvailable && !isBundleAvailable" class="bg-red-50 border border-red-200 text-red-700 p-3 rounded-xl text-xs font-bold flex items-center gap-2">
                            <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                            <span>At least one purchase option must be enabled.</span>
                        </div>

                        <!-- AVAILABLE PURCHASE OPTIONS: 3 PRODUCT OFFERINGS -->
                        <div>
                            <div class="flex items-center justify-between mb-3">
                                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wide">
                                    Available Purchase Options (3 Product Types)
                                </label>
                                <span class="text-[11px] text-gray-400">Toggle offering status &amp; set standalone pricing</span>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                                
                                <!-- Product 1: PDF Only -->
                                <div class="rounded-xl border p-4.5 transition-all space-y-3"
                                     :class="isPdfAvailable ? 'border-gray-300 bg-white shadow-sm ring-1 ring-emerald-500/20' : 'border-gray-200 bg-gray-50 opacity-60'">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-2">
                                            <span class="w-2.5 h-2.5 rounded-full" :class="isPdfAvailable ? 'bg-emerald-500' : 'bg-gray-300'"></span>
                                            <span class="text-xs font-bold uppercase" :class="isPdfAvailable ? 'text-navy' : 'text-gray-500'">PDF Study Guide</span>
                                        </div>
                                        <label class="relative inline-flex items-center cursor-pointer">
                                            <input type="checkbox" name="is_pdf_available" value="1" x-model="isPdfAvailable" @change="markDirty()" class="sr-only peer">
                                            <div class="w-9 h-5 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-emerald-500"></div>
                                        </label>
                                    </div>
                                    <p class="text-[11px] text-gray-500">Printable &amp; mobile digital study guide with verified answer keys.</p>
                                    <div>
                                        <label for="price_pdf" class="block text-[10px] font-bold uppercase text-gray-600 mb-1">
                                            PDF Price ($) <span x-show="isPdfAvailable" class="text-red-500">*</span>
                                        </label>
                                        <div class="relative rounded-lg shadow-sm">
                                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400 font-bold">$</div>
                                            <input type="number" step="0.01" min="0" name="price_pdf" id="price_pdf" x-model="pricePdf" :disabled="!isPdfAvailable" @input="markDirty()"
                                                   placeholder="29.00"
                                                   class="w-full border-gray-300 rounded-lg text-sm pl-7 pr-3 py-2 font-bold font-mono text-navy focus:border-cyan focus:ring-cyan disabled:bg-gray-100 disabled:text-gray-400">
                                        </div>
                                        @error('price_pdf') <p class="text-red-500 text-xs mt-1 font-medium">{{ $message }}</p> @enderror
                                    </div>
                                    <div class="text-[10px] font-bold" :class="isPdfAvailable ? 'text-emerald-700' : 'text-gray-400'" x-text="isPdfAvailable ? '✓ Offered to Customers' : '✕ Disabled (Hidden from checkout)'"></div>
                                </div>

                                <!-- Product 2: Simulator Only -->
                                <div class="rounded-xl border p-4.5 transition-all space-y-3"
                                     :class="isEngineAvailable ? 'border-gray-300 bg-white shadow-sm ring-1 ring-cyan/30' : 'border-gray-200 bg-gray-50 opacity-60'">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-2">
                                            <span class="w-2.5 h-2.5 rounded-full" :class="isEngineAvailable ? 'bg-cyan' : 'bg-gray-300'"></span>
                                            <span class="text-xs font-bold uppercase" :class="isEngineAvailable ? 'text-navy' : 'text-gray-500'">Test Engine Simulator</span>
                                        </div>
                                        <label class="relative inline-flex items-center cursor-pointer">
                                            <input type="checkbox" name="is_engine_available" value="1" x-model="isEngineAvailable" @change="markDirty()" class="sr-only peer">
                                            <div class="w-9 h-5 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-cyan"></div>
                                        </label>
                                    </div>
                                    <p class="text-[11px] text-gray-500">Interactive timed practice simulator with scoring &amp; question review.</p>
                                    <div>
                                        <label for="price_engine" class="block text-[10px] font-bold uppercase text-gray-600 mb-1">
                                            Simulator Price ($) <span x-show="isEngineAvailable" class="text-red-500">*</span>
                                        </label>
                                        <div class="relative rounded-lg shadow-sm">
                                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400 font-bold">$</div>
                                            <input type="number" step="0.01" min="0" name="price_engine" id="price_engine" x-model="priceEngine" :disabled="!isEngineAvailable" @input="markDirty()"
                                                   placeholder="39.00"
                                                   class="w-full border-gray-300 rounded-lg text-sm pl-7 pr-3 py-2 font-bold font-mono text-navy focus:border-cyan focus:ring-cyan disabled:bg-gray-100 disabled:text-gray-400">
                                        </div>
                                        @error('price_engine') <p class="text-red-500 text-xs mt-1 font-medium">{{ $message }}</p> @enderror
                                    </div>
                                    <div class="text-[10px] font-bold" :class="isEngineAvailable ? 'text-cyan' : 'text-gray-400'" x-text="isEngineAvailable ? '✓ Offered to Customers' : '✕ Disabled (Hidden from checkout)'"></div>
                                </div>

                                <!-- Product 3: PDF + Simulator Bundle -->
                                <div class="rounded-xl border p-4.5 transition-all space-y-3"
                                     :class="isBundleAvailable ? 'border-purple-300 bg-purple-50/20 shadow-sm ring-1 ring-purple-500/30' : 'border-gray-200 bg-gray-50 opacity-60'">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-2">
                                            <span class="w-2.5 h-2.5 rounded-full" :class="isBundleAvailable ? 'bg-purple-600' : 'bg-gray-300'"></span>
                                            <span class="text-xs font-bold uppercase" :class="isBundleAvailable ? 'text-purple-950' : 'text-gray-500'">PDF + Engine Bundle</span>
                                        </div>
                                        <label class="relative inline-flex items-center cursor-pointer">
                                            <input type="checkbox" name="is_bundle_available" value="1" x-model="isBundleAvailable" @change="markDirty()" class="sr-only peer">
                                            <div class="w-9 h-5 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-purple-600"></div>
                                        </label>
                                    </div>
                                    <p class="text-[11px] text-gray-500">Combined prep package granting dual access. Configured independently.</p>
                                    <div>
                                        <label for="price_bundle" class="block text-[10px] font-bold uppercase text-gray-600 mb-1">
                                            Bundle Price ($) <span x-show="isBundleAvailable" class="text-red-500">*</span>
                                        </label>
                                        <div class="relative rounded-lg shadow-sm">
                                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400 font-bold">$</div>
                                            <input type="number" step="0.01" min="0" name="price_bundle" id="price_bundle" x-model="priceBundle" :disabled="!isBundleAvailable" @input="markDirty()"
                                                   placeholder="59.00"
                                                   class="w-full border-gray-300 rounded-lg text-sm pl-7 pr-3 py-2 font-bold font-mono text-navy focus:border-cyan focus:ring-cyan disabled:bg-gray-100 disabled:text-gray-400">
                                        </div>
                                        @error('price_bundle') <p class="text-red-500 text-xs mt-1 font-medium">{{ $message }}</p> @enderror
                                    </div>
                                    <div class="text-[10px] font-bold" :class="isBundleAvailable ? 'text-purple-700' : 'text-gray-400'" x-text="isBundleAvailable ? '✓ Offered to Customers' : '✕ Disabled (Hidden from checkout)'"></div>
                                </div>

                            </div>
                        </div>

                        <!-- Structured Update Plans Table -->
                        <div class="space-y-3 pt-2">
                            <div class="flex items-center justify-between">
                                <label class="block text-xs font-bold text-gray-700 uppercase">
                                    Subscription / Update Extension Plans
                                </label>
                                <span class="text-[11px] text-gray-400">Post-purchase update extension rates</span>
                            </div>

                            <div class="border border-gray-200 rounded-xl overflow-hidden shadow-sm">
                                <table class="min-w-full divide-y divide-gray-200 text-left text-xs">
                                    <thead class="bg-gray-100/80 font-bold uppercase text-gray-600 tracking-wider">
                                        <tr>
                                            <th class="px-4 py-3">Plan Duration</th>
                                            <th class="px-4 py-3">Features & Entitlement</th>
                                            <th class="px-4 py-3 text-right">Extension Fee ($)</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100 bg-white">
                                        <tr>
                                            <td class="px-4 py-3.5 font-bold text-navy flex items-center gap-2">
                                                <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                                3 Months Updates
                                            </td>
                                            <td class="px-4 py-3.5 text-gray-500">Included complimentary with original purchase</td>
                                            <td class="px-4 py-3.5 text-right font-mono">
                                                <div class="inline-flex items-center justify-end">
                                                    <span class="text-gray-400 mr-1">$</span>
                                                    <input type="number" step="0.01" min="0" name="update_price_3_months" x-model="update3" @input="markDirty()"
                                                           class="w-24 text-right border-gray-200 rounded-md text-xs py-1 px-2 font-bold font-mono focus:border-cyan focus:ring-cyan">
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="px-4 py-3.5 font-bold text-navy flex items-center gap-2">
                                                <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                                                6 Months Updates
                                            </td>
                                            <td class="px-4 py-3.5 text-gray-500">Extended 6 months continuous question pool updates</td>
                                            <td class="px-4 py-3.5 text-right font-mono">
                                                <div class="inline-flex items-center justify-end">
                                                    <span class="text-gray-400 mr-1">$</span>
                                                    <input type="number" step="0.01" min="0" name="update_price_6_months" x-model="update6" @input="markDirty()"
                                                           class="w-24 text-right border-gray-200 rounded-md text-xs py-1 px-2 font-bold font-mono focus:border-cyan focus:ring-cyan">
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="px-4 py-3.5 font-bold text-navy flex items-center gap-2">
                                                <span class="w-2 h-2 rounded-full bg-purple-500"></span>
                                                12 Months Updates
                                            </td>
                                            <td class="px-4 py-3.5 text-gray-500">Full 1 year guaranteed access with new release revisions</td>
                                            <td class="px-4 py-3.5 text-right font-mono">
                                                <div class="inline-flex items-center justify-end">
                                                    <span class="text-gray-400 mr-1">$</span>
                                                    <input type="number" step="0.01" min="0" name="update_price_12_months" x-model="update12" @input="markDirty()"
                                                           class="w-24 text-right border-gray-200 rounded-md text-xs py-1 px-2 font-bold font-mono focus:border-cyan focus:ring-cyan">
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 04: EXAM CONTENT & SYLLABUS -->
                <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden" id="section-content">
                    <div class="border-b border-gray-100 px-6 py-4 bg-gray-50/50 flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <span class="flex items-center justify-center w-7 h-7 rounded-md bg-navy text-white text-xs font-black">04</span>
                            <div>
                                <h3 class="text-sm font-bold text-navy uppercase tracking-wide">Exam Content & Syllabus</h3>
                                <p class="text-xs text-gray-500">Define curriculum objectives, knowledge domains, and full course description.</p>
                            </div>
                        </div>
                    </div>

                    <div class="p-6 space-y-6">
                        <!-- Syllabus Topics Interactive Chips Manager -->
                        <div class="space-y-3">
                            <div class="flex items-center justify-between">
                                <label class="block text-xs font-bold text-gray-700 uppercase">
                                    Exam Syllabus Topics / Domains
                                </label>
                                <span class="text-[11px] text-gray-400" x-text="topics.length + ' domains configured'"></span>
                            </div>

                            <!-- Add Topic Input -->
                            <div class="flex gap-2">
                                <input type="text" x-model="newTopicInput" @keydown.enter.prevent="addTopic()" placeholder="Add a topic (e.g. Network Fundamentals) and press Enter"
                                       class="flex-1 border-gray-300 rounded-lg text-sm px-3.5 py-2 focus:border-cyan focus:ring-cyan shadow-sm">
                                <button type="button" @click="addTopic()" class="px-4 py-2 bg-navy text-white text-xs font-bold rounded-lg hover:bg-gray-800 transition shrink-0">
                                    + Add Topic
                                </button>
                            </div>

                            <!-- Topic Badges Container -->
                            <div class="min-h-[46px] p-3 border border-dashed border-gray-200 rounded-lg bg-gray-50/60 flex flex-wrap gap-2 items-center">
                                <template x-for="(topic, index) in topics" :key="index">
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-white text-navy border border-gray-200 shadow-sm">
                                        <span class="w-1.5 h-1.5 rounded-full bg-cyan"></span>
                                        <span x-text="topic"></span>
                                        <button type="button" @click="removeTopic(index)" class="text-gray-400 hover:text-red-500 ml-1">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                                        </button>
                                        <input type="hidden" name="topics[]" :value="topic">
                                    </span>
                                </template>
                                <span x-show="topics.length === 0" class="text-xs text-gray-400 italic">No topics added yet. Add domain headings above.</span>
                            </div>
                        </div>

                        <!-- Exam Description TipTap Rich Text Editor -->
                        <div class="space-y-2">
                            <div class="flex items-center justify-between">
                                <label class="block text-xs font-bold text-gray-700 uppercase">
                                    Exam Overview & Description
                                </label>
                                <span class="text-[11px] text-gray-400">Rich text formatting with TipTap</span>
                            </div>

                            <div class="tiptap-container border border-gray-300 rounded-xl overflow-hidden shadow-sm" data-content="{{ base64_encode(old('description', '')) }}">
                                <!-- Toolbar -->
                                <div class="bg-gray-50 border-b border-gray-200 px-3 py-2 flex flex-wrap items-center gap-1">
                                    <button type="button" class="btn-bold p-1.5 rounded hover:bg-gray-200 text-gray-600 transition" title="Bold">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 4h8a4 4 0 014 4 4 4 0 01-4 4H6z M6 12h9a4 4 0 014 4 4 4 0 01-4 4H6z"/></svg>
                                    </button>
                                    <button type="button" class="btn-italic p-1.5 rounded hover:bg-gray-200 text-gray-600 transition" title="Italic">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 20l4-16m4 4l-1.5 6m-5.5 2h5M8 4h5"/></svg>
                                    </button>
                                    <div class="w-px h-5 bg-gray-300 mx-1"></div>
                                    <button type="button" class="btn-p px-2 py-1 rounded text-xs font-bold text-gray-600 hover:bg-gray-200 transition" title="Paragraph">P</button>
                                    <button type="button" class="btn-h1 px-2 py-1 rounded text-xs font-bold text-gray-600 hover:bg-gray-200 transition" title="Heading 1">H1</button>
                                    <button type="button" class="btn-h2 px-2 py-1 rounded text-xs font-bold text-gray-600 hover:bg-gray-200 transition" title="Heading 2">H2</button>
                                    <button type="button" class="btn-h3 px-2 py-1 rounded text-xs font-bold text-gray-600 hover:bg-gray-200 transition" title="Heading 3">H3</button>
                                    <div class="w-px h-5 bg-gray-300 mx-1"></div>
                                    <button type="button" class="btn-bullet p-1.5 rounded hover:bg-gray-200 text-gray-600 transition" title="Bullet List">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                                    </button>
                                    <button type="button" class="btn-ordered p-1.5 rounded hover:bg-gray-200 text-gray-600 transition" title="Numbered List">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H21M9 12H21M9 19H21M5 5.01V5M5 12.01V12M5 19.01V19"/></svg>
                                    </button>
                                    <button type="button" class="btn-quote p-1.5 rounded hover:bg-gray-200 text-gray-600 transition" title="Quote">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/></svg>
                                    </button>
                                    <div class="w-px h-5 bg-gray-300 mx-1"></div>
                                    <button type="button" class="btn-link p-1.5 rounded hover:bg-gray-200 text-gray-600 transition" title="Link">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
                                    </button>
                                </div>

                                <!-- Editor Content Body -->
                                <div class="editor-element min-h-[220px] p-4 bg-white text-gray-800 text-sm focus:outline-none"></div>

                                <!-- Hidden input for form submit -->
                                <input type="hidden" name="description" class="content-input" value="{{ old('description', '') }}">
                            </div>
                            @error('description') <p class="text-red-500 text-xs mt-1.5 font-medium">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>

                <!-- 05: PDF & DIGITAL ASSETS -->
                <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden" id="section-files">
                    <div class="border-b border-gray-100 px-6 py-4 bg-gray-50/50 flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <span class="flex items-center justify-center w-7 h-7 rounded-md bg-navy text-white text-xs font-black">05</span>
                            <div>
                                <h3 class="text-sm font-bold text-navy uppercase tracking-wide">PDF & Digital Assets</h3>
                                <p class="text-xs text-gray-500">Manage demo previews and authenticated full customer download guides.</p>
                            </div>
                        </div>
                    </div>

                    <div class="p-6 space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            
                            <!-- Demo PDF Dropzone -->
                            <div class="p-5 border border-gray-200 rounded-xl bg-gray-50/40 space-y-3">
                                <div class="flex items-center justify-between">
                                    <label class="block text-xs font-bold text-gray-700 uppercase">
                                        Free Sample / Demo PDF
                                    </label>
                                    <span class="text-[10px] uppercase font-bold text-emerald-700 bg-emerald-100 px-2 py-0.5 rounded">Public Sample</span>
                                </div>

                                <!-- Drag & Drop File Input -->
                                <div>
                                    <label class="block text-[11px] font-semibold text-gray-500 mb-1">
                                        Upload Demo PDF Guide:
                                    </label>
                                    <input type="file" name="demo_pdf" id="demo_pdf" accept=".pdf"
                                           class="w-full text-xs text-gray-500 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-navy file:text-white hover:file:bg-gray-800 cursor-pointer border border-gray-200 rounded-lg p-1 bg-white">
                                    <p class="text-[10px] text-gray-400 mt-1.5">Max allowed size: 20MB. Standard PDF only.</p>
                                    @error('demo_pdf') <p class="text-red-500 text-xs mt-1 font-medium">{{ $message }}</p> @enderror
                                </div>
                            </div>

                            <!-- Full Access PDF Dropzone -->
                            <div class="p-5 border border-gray-200 rounded-xl bg-gray-50/40 space-y-3">
                                <div class="flex items-center justify-between">
                                    <label class="block text-xs font-bold text-gray-700 uppercase">
                                        Full Access Study Guide PDF
                                    </label>
                                    <span class="text-[10px] uppercase font-bold text-navy bg-navy/10 px-2 py-0.5 rounded">Paid Protected</span>
                                </div>

                                <!-- Drag & Drop File Input -->
                                <div>
                                    <label class="block text-[11px] font-semibold text-gray-500 mb-1">
                                        Upload Complete PDF Guide:
                                    </label>
                                    <input type="file" name="full_pdf" id="full_pdf" accept=".pdf"
                                           class="w-full text-xs text-gray-500 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-navy file:text-white hover:file:bg-gray-800 cursor-pointer border border-gray-200 rounded-lg p-1 bg-white">
                                    <p class="text-[10px] text-gray-400 mt-1.5">Max allowed size: 50MB. Sent exclusively to paid users.</p>
                                    @error('full_pdf') <p class="text-red-500 text-xs mt-1 font-medium">{{ $message }}</p> @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 06: SEARCH ENGINE OPTIMIZATION (SEO) -->
                <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden" x-data="{ seoOpen: true }" id="section-seo">
                    <div class="border-b border-gray-100 px-6 py-4 bg-gray-50/50 flex items-center justify-between cursor-pointer" @click="seoOpen = !seoOpen">
                        <div class="flex items-center space-x-3">
                            <span class="flex items-center justify-center w-7 h-7 rounded-md bg-navy text-white text-xs font-black">06</span>
                            <div>
                                <h3 class="text-sm font-bold text-navy uppercase tracking-wide">Search Engine Optimization (SEO)</h3>
                                <p class="text-xs text-gray-500">Fine-tune Google SERP snippets, meta tags, and search index previews.</p>
                            </div>
                        </div>
                        <button type="button" class="text-gray-400 hover:text-navy">
                            <svg class="w-5 h-5 transform transition-transform" :class="seoOpen ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                        </button>
                    </div>

                    <div x-show="seoOpen" class="p-6 space-y-6">
                        <!-- Meta Title -->
                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <label for="meta_title" class="block text-xs font-bold text-gray-700 uppercase">
                                    SEO Meta Title
                                </label>
                                <span class="text-xs font-mono font-bold" :class="metaTitle.length >= 50 && metaTitle.length <= 60 ? 'text-emerald-600' : 'text-gray-400'">
                                    <span x-text="metaTitle.length"></span> / 60 chars (recommended)
                                </span>
                            </div>
                            <input type="text" name="meta_title" id="meta_title" x-model="metaTitle" @input="markDirty()"
                                   placeholder="e.g. Best Cisco CCNA 200-301 Practice Exams & Study Guide"
                                   class="w-full border-gray-300 rounded-lg text-sm px-3.5 py-2.5 focus:border-cyan focus:ring-cyan shadow-sm">
                            @error('meta_title') <p class="text-red-500 text-xs mt-1.5 font-medium">{{ $message }}</p> @enderror
                        </div>

                        <!-- Meta Description -->
                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <label for="meta_description" class="block text-xs font-bold text-gray-700 uppercase">
                                    SEO Meta Description
                                </label>
                                <span class="text-xs font-mono font-bold" :class="metaDescription.length >= 140 && metaDescription.length <= 160 ? 'text-emerald-600' : 'text-gray-400'">
                                    <span x-text="metaDescription.length"></span> / 160 chars (recommended)
                                </span>
                            </div>
                            <textarea name="meta_description" id="meta_description" rows="2" x-model="metaDescription" @input="markDirty()"
                                      placeholder="e.g. Pass your Cisco 200-301 CCNA certification on the first attempt with 100% verified practice questions, simulator, and study guide."
                                      class="w-full border-gray-300 rounded-lg text-sm px-3.5 py-2.5 focus:border-cyan focus:ring-cyan shadow-sm"></textarea>
                            @error('meta_description') <p class="text-red-500 text-xs mt-1.5 font-medium">{{ $message }}</p> @enderror
                        </div>

                        <!-- Meta Keywords -->
                        <div>
                            <label for="meta_keywords" class="block text-xs font-bold text-gray-700 uppercase mb-2">
                                SEO Meta Keywords (Comma-separated)
                            </label>
                            <input type="text" name="meta_keywords" id="meta_keywords" x-model="metaKeywords" @input="markDirty()"
                                   placeholder="e.g. cisco ccna, 200-301 dumps, practice test, exam simulator"
                                   class="w-full border-gray-300 rounded-lg text-sm px-3.5 py-2.5 focus:border-cyan focus:ring-cyan shadow-sm">
                            @error('meta_keywords') <p class="text-red-500 text-xs mt-1.5 font-medium">{{ $message }}</p> @enderror
                        </div>

                        <!-- Live Google SERP Search Preview Card -->
                        <div class="bg-gray-50 border border-gray-200 rounded-xl p-5 space-y-2">
                            <div class="flex items-center justify-between text-[11px] font-bold uppercase tracking-wider text-gray-400 mb-1">
                                <span>Google Search Result Preview</span>
                                <span class="text-emerald-600">Simulated SERP</span>
                            </div>
                            
                            <div class="space-y-1">
                                <div class="flex items-center space-x-2 text-xs text-[#202124]">
                                    <div class="w-4 h-4 rounded-full bg-cyan/20 flex items-center justify-center text-[10px] font-bold text-navy">E</div>
                                    <span class="text-xs text-[#202124] font-medium">{{ request()->getHost() }}</span>
                                    <span class="text-gray-400">› exams › <span x-text="slug || (examCode ? examCode.toLowerCase() : 'exam')"></span></span>
                                </div>
                                <h4 class="text-base text-[#1a0dab] hover:underline font-medium cursor-pointer truncate"
                                    x-text="metaTitle || (examCode ? examCode + ' - ' + examName + ' Study Guide' : 'Exam Title')"></h4>
                                <p class="text-xs text-[#4d5156] line-clamp-2 leading-relaxed"
                                   x-text="metaDescription || 'Get updated ' + (examCode || '') + ' (' + (examName || '') + ') exam questions, answers, and study guides. Try our free demo or web-based test engine.'"></p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 07: ADVANCED SETTINGS -->
                <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden" x-data="{ advancedOpen: false }" id="section-advanced">
                    <div class="border-b border-gray-100 px-6 py-4 bg-gray-50/50 flex items-center justify-between cursor-pointer" @click="advancedOpen = !advancedOpen">
                        <div class="flex items-center space-x-3">
                            <span class="flex items-center justify-center w-7 h-7 rounded-md bg-navy text-white text-xs font-black">07</span>
                            <div>
                                <h3 class="text-sm font-bold text-navy uppercase tracking-wide">Advanced & Technical Settings</h3>
                                <p class="text-xs text-gray-500">URL slugs, sort order, featured status, and internal administration notes.</p>
                            </div>
                        </div>
                        <button type="button" class="text-gray-400 hover:text-navy">
                            <svg class="w-5 h-5 transform transition-transform" :class="advancedOpen ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                        </button>
                    </div>

                    <div x-show="advancedOpen" class="p-6 space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Custom URL Slug -->
                            <div>
                                <label for="slug" class="block text-xs font-bold text-gray-700 uppercase mb-2">
                                    Custom URL Slug
                                </label>
                                <input type="text" name="slug" id="slug" x-model="slug" @input="markDirty()"
                                       placeholder="Leave empty to auto-slugify from exam code"
                                       class="w-full border-gray-300 rounded-lg text-sm px-3.5 py-2.5 font-mono text-navy focus:border-cyan focus:ring-cyan shadow-sm">
                                <p class="text-[11px] text-gray-400 mt-1">Leave empty to auto-generate from exam code.</p>
                                @error('slug') <p class="text-red-500 text-xs mt-1.5 font-medium">{{ $message }}</p> @enderror
                            </div>

                            <!-- Sort Order -->
                            <div>
                                <label for="sort_order" class="block text-xs font-bold text-gray-700 uppercase mb-2">
                                    Catalog Sort Priority
                                </label>
                                <input type="number" name="sort_order" id="sort_order" x-model.number="sortOrder" @input="markDirty()" min="0"
                                       class="w-full border-gray-300 rounded-lg text-sm px-3.5 py-2.5 font-mono text-navy focus:border-cyan focus:ring-cyan shadow-sm">
                                <p class="text-[11px] text-gray-400 mt-1">Lower numbers appear first in lists. Default is 0.</p>
                                @error('sort_order') <p class="text-red-500 text-xs mt-1.5 font-medium">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <!-- Featured Exam Toggle -->
                        <div class="flex items-center p-4 bg-purple-50/50 border border-purple-100 rounded-lg">
                            <input type="checkbox" name="is_featured" id="is_featured" value="1" x-model="isFeatured" @change="markDirty()"
                                   class="rounded border-gray-300 text-purple-600 focus:ring-purple-500 h-4 w-4">
                            <label for="is_featured" class="ml-3 text-xs font-bold text-purple-900 cursor-pointer">
                                Mark as Featured Exam (Highlighted in top certification lists & badges)
                            </label>
                        </div>

                        <!-- Internal Admin Notes -->
                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <label for="admin_notes" class="block text-xs font-bold text-gray-700 uppercase">
                                    Internal Admin Notes
                                </label>
                                <span class="text-[10px] font-bold text-red-600 uppercase bg-red-50 px-2 py-0.5 rounded">Private / Not Customer Visible</span>
                            </div>
                            <textarea name="admin_notes" id="admin_notes" rows="3" placeholder="Add confidential notes regarding question pool updates, source material, or vendor revision dates..."
                                      class="w-full border-gray-300 rounded-lg text-sm px-3.5 py-2.5 focus:border-cyan focus:ring-cyan shadow-sm">{{ old('admin_notes', '') }}</textarea>
                            @error('admin_notes') <p class="text-red-500 text-xs mt-1.5 font-medium">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>

            </div>

            <!-- Right Column: 30% Sticky Publishing & Summary Console -->
            <div class="lg:col-span-4 space-y-6 lg:sticky lg:top-6">

                <!-- Sticky Publishing Card -->
                <div class="bg-white border border-gray-200 rounded-xl p-6 shadow-sm space-y-5">
                    <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                        <h3 class="text-xs font-black text-navy uppercase tracking-wider">Publishing Console</h3>
                        <span x-show="isDirty" class="inline-flex items-center text-[10px] font-bold text-amber-600 bg-amber-50 border border-amber-200 px-2 py-0.5 rounded-full">
                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500 mr-1 animate-pulse"></span>
                            Unsaved Changes
                        </span>
                    </div>

                    <!-- Status Selector -->
                    <div>
                        <label class="block text-[11px] font-bold text-gray-500 uppercase mb-2">Visibility Status</label>
                        <div class="grid grid-cols-2 gap-2">
                            <button type="button" @click="isActive = true; markDirty()"
                                    :class="isActive ? 'bg-emerald-600 text-white shadow-sm border-emerald-600 font-bold' : 'bg-white text-gray-600 border-gray-200 hover:bg-gray-50'"
                                    class="px-3 py-2 rounded-lg border text-xs text-center transition flex items-center justify-center gap-1.5">
                                <span class="w-2 h-2 rounded-full" :class="isActive ? 'bg-white' : 'bg-emerald-500'"></span>
                                Active / Live
                            </button>
                            <button type="button" @click="isActive = false; markDirty()"
                                    :class="!isActive ? 'bg-navy text-white shadow-sm border-navy font-bold' : 'bg-white text-gray-600 border-gray-200 hover:bg-gray-50'"
                                    class="px-3 py-2 rounded-lg border text-xs text-center transition flex items-center justify-center gap-1.5">
                                <span class="w-2 h-2 rounded-full" :class="!isActive ? 'bg-white' : 'bg-gray-400'"></span>
                                Draft / Hidden
                            </button>
                        </div>
                        <input type="hidden" name="is_active" :value="isActive ? '1' : '0'">
                    </div>

                    <!-- Action Buttons -->
                    <div class="space-y-2 pt-2">
                        <button type="submit" @click="document.getElementById('formActionInput').value = 'publish'"
                                class="w-full bg-navy hover:bg-gray-800 text-white font-bold py-3 px-4 rounded-xl text-sm shadow-md hover:shadow-lg transition flex items-center justify-center gap-2">
                            <svg class="w-4 h-4 text-cyan" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" /></svg>
                            <span>Create & Publish Exam</span>
                        </button>

                        <button type="submit" @click="document.getElementById('formActionInput').value = 'draft'; isActive = false"
                                class="w-full bg-white hover:bg-gray-50 border border-gray-300 text-gray-700 font-bold py-2.5 px-4 rounded-xl text-xs transition shadow-sm flex items-center justify-center gap-2">
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4" /></svg>
                            <span>Save as Draft</span>
                        </button>
                    </div>

                    <div class="pt-3 border-t border-gray-100 text-[11px] text-gray-400 text-center">
                        Exams saved as draft remain hidden from public search and checkout.
                    </div>
                </div>

                <!-- Live Dynamic Exam Summary Card -->
                <div class="bg-white border border-gray-200 rounded-xl p-6 shadow-sm space-y-4">
                    <h3 class="text-xs font-black text-navy uppercase tracking-wider border-b border-gray-100 pb-3 flex items-center justify-between">
                        <span>Exam Summary</span>
                        <span class="text-[10px] font-mono text-cyan bg-cyan/10 px-2 py-0.5 rounded">Live Sync</span>
                    </h3>

                    <div class="space-y-3 text-xs">
                        <div class="flex items-center justify-between py-1 border-b border-gray-50">
                            <span class="text-gray-500">Exam Code:</span>
                            <span class="font-mono font-bold text-navy" x-text="examCode || '---'"></span>
                        </div>
                        <div class="flex items-center justify-between py-1 border-b border-gray-50">
                            <span class="text-gray-500">Vendor:</span>
                            <span class="font-bold text-gray-800" x-text="vendorName || '---'"></span>
                        </div>
                        <div class="flex items-center justify-between py-1 border-b border-gray-50" x-show="headerTitle && headerTitle.trim().length > 0">
                            <span class="text-gray-500">Custom H1:</span>
                            <span class="font-bold text-cyan truncate max-w-[130px]" x-text="headerTitle" :title="headerTitle"></span>
                        </div>
                        <div class="flex items-center justify-between py-1 border-b border-gray-50">
                            <span class="text-gray-500">Difficulty:</span>
                            <span class="font-bold" :class="difficulty === 'Expert' ? 'text-purple-600' : (difficulty === 'Professional' ? 'text-blue-600' : 'text-emerald-600')" x-text="difficulty"></span>
                        </div>
                        <div class="flex items-center justify-between py-1 border-b border-gray-50">
                            <span class="text-gray-500">Passing Score:</span>
                            <span class="font-mono font-bold text-navy" x-text="passingScore + '%'"></span>
                        </div>
                        <!-- 3 Product Offerings Status & Prices -->
                        <div class="pt-2 border-t border-gray-100 space-y-1.5">
                            <span class="text-[10px] uppercase font-bold text-gray-400">Available Products:</span>
                            <div class="flex items-center justify-between text-xs">
                                <span class="flex items-center gap-1.5" :class="isPdfAvailable ? 'text-gray-800 font-medium' : 'text-gray-400 line-through'">
                                    <span :class="isPdfAvailable ? 'text-emerald-500 font-bold' : 'text-gray-300'">✓</span> PDF Guide
                                </span>
                                <span class="font-mono font-bold" :class="isPdfAvailable ? 'text-navy' : 'text-gray-400'" x-text="isPdfAvailable ? ('$' + (pricePdf || '0.00')) : 'Disabled'"></span>
                            </div>
                            <div class="flex items-center justify-between text-xs">
                                <span class="flex items-center gap-1.5" :class="isEngineAvailable ? 'text-gray-800 font-medium' : 'text-gray-400 line-through'">
                                    <span :class="isEngineAvailable ? 'text-cyan font-bold' : 'text-gray-300'">✓</span> Simulator
                                </span>
                                <span class="font-mono font-bold" :class="isEngineAvailable ? 'text-navy' : 'text-gray-400'" x-text="isEngineAvailable ? ('$' + (priceEngine || '0.00')) : 'Disabled'"></span>
                            </div>
                            <div class="flex items-center justify-between text-xs">
                                <span class="flex items-center gap-1.5" :class="isBundleAvailable ? 'text-gray-800 font-medium' : 'text-gray-400 line-through'">
                                    <span :class="isBundleAvailable ? 'text-purple-600 font-bold' : 'text-gray-300'">✓</span> PDF + Simulator
                                </span>
                                <span class="font-mono font-bold" :class="isBundleAvailable ? 'text-navy' : 'text-gray-400'" x-text="isBundleAvailable ? ('$' + (priceBundle || '0.00')) : 'Disabled'"></span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Content Quality Completeness Score Card -->
                <div class="bg-white border border-gray-200 rounded-xl p-6 shadow-sm space-y-4">
                    <div class="flex items-center justify-between">
                        <h3 class="text-xs font-black text-navy uppercase tracking-wider">Content Quality</h3>
                        <span class="text-xs font-black" :class="completenessScore >= 80 ? 'text-emerald-600' : 'text-amber-600'" x-text="completenessScore + '%'"></span>
                    </div>

                    <!-- Progress Bar -->
                    <div class="w-full bg-gray-100 rounded-full h-2.5 overflow-hidden">
                        <div class="h-2.5 rounded-full transition-all duration-500"
                             :class="completenessScore >= 80 ? 'bg-emerald-500' : (completenessScore >= 50 ? 'bg-amber-500' : 'bg-red-500')"
                             :style="'width: ' + completenessScore + '%'"></div>
                    </div>

                    <!-- Micro-Checklist -->
                    <div class="space-y-1.5 pt-1 text-[11px]">
                        <div class="flex items-center justify-between" :class="examCode && examName && vendorId ? 'text-emerald-700' : 'text-gray-400'">
                            <span>Basic Information (Code, Name, Vendor)</span>
                            <span x-text="examCode && examName && vendorId ? '✓' : '○'"></span>
                        </div>
                        <div class="flex items-center justify-between" :class="pricePdf > 0 && priceEngine > 0 ? 'text-emerald-700' : 'text-gray-400'">
                            <span>Pricing Configured</span>
                            <span x-text="pricePdf > 0 && priceEngine > 0 ? '✓' : '○'"></span>
                        </div>
                        <div class="flex items-center justify-between" :class="passingScore > 0 ? 'text-emerald-700' : 'text-gray-400'">
                            <span>Passing Score Configured</span>
                            <span x-text="passingScore > 0 ? '✓' : '○'"></span>
                        </div>
                        <div class="flex items-center justify-between" :class="metaTitle && metaDescription ? 'text-emerald-700' : 'text-gray-400'">
                            <span>SEO Metadata Completed</span>
                            <span x-text="metaTitle && metaDescription ? '✓' : '○'"></span>
                        </div>
                    </div>
                </div>

            </div>

        </div>

        <!-- Sticky Bottom Mobile Actions Bar -->
        <div class="lg:hidden fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 p-4 shadow-2xl z-40 flex items-center justify-between gap-3">
            <a href="{{ route('admin.exams.index') }}" class="px-3 py-2 text-xs font-bold text-gray-600">Cancel</a>
            <div class="flex items-center gap-2">
                <button type="submit" @click="document.getElementById('formActionInput').value = 'draft'; isActive = false"
                        class="px-3 py-2 rounded-lg border border-gray-300 bg-white text-xs font-bold text-gray-700">
                    Save Draft
                </button>
                <button type="submit" @click="document.getElementById('formActionInput').value = 'publish'"
                        class="px-4 py-2 rounded-lg bg-navy text-xs font-bold text-white shadow">
                    Create Exam
                </button>
            </div>
        </div>
    </form>

</div>
@endsection

@section('scripts')
<script>
function examWorkspace(initial) {
    return {
        examCode: initial.examCode,
        examName: initial.examName,
        headerTitle: initial.headerTitle,
        vendorId: initial.vendorId,
        vendorName: initial.vendorName,
        difficulty: initial.difficulty,
        examType: initial.examType,
        passingScore: initial.passingScore,
        questionCount: initial.questionCount,
        actualQuestions: initial.actualQuestions,
        isPdfAvailable: initial.isPdfAvailable,
        isEngineAvailable: initial.isEngineAvailable,
        isBundleAvailable: initial.isBundleAvailable,
        pricePdf: initial.pricePdf,
        priceEngine: initial.priceEngine,
        priceBundle: initial.priceBundle,
        update3: initial.update3,
        update6: initial.update6,
        update12: initial.update12,
        isActive: initial.isActive,
        isFeatured: initial.isFeatured,
        slug: initial.slug,
        sortOrder: initial.sortOrder,
        metaTitle: initial.metaTitle,
        metaDescription: initial.metaDescription,
        metaKeywords: initial.metaKeywords,
        hasDemoPdf: initial.hasDemoPdf,
        hasFullPdf: initial.hasFullPdf,
        topics: initial.topics || [],
        newTopicInput: '',
        isDirty: false,
        isSubmitting: false,

        init() {
            window.addEventListener('beforeunload', (e) => {
                if (this.isDirty && !this.isSubmitting) {
                    e.preventDefault();
                    e.returnValue = '';
                }
            });
            // Auto initialize vendor name if pre-selected
            this.$nextTick(() => {
                const el = document.getElementById('vendor_id');
                if (el && el.selectedIndex > 0) {
                    this.vendorName = el.options[el.selectedIndex].getAttribute('data-name') || '';
                }
            });
        },

        markDirty() {
            this.isDirty = true;
        },

        onVendorChange(e) {
            const selectedOpt = e.target.options[e.target.selectedIndex];
            if (selectedOpt) {
                this.vendorName = selectedOpt.getAttribute('data-name') || '';
            }
            this.markDirty();
        },

        addTopic() {
            const trimmed = this.newTopicInput.trim();
            if (trimmed && !this.topics.includes(trimmed)) {
                this.topics.push(trimmed);
                this.newTopicInput = '';
                this.markDirty();
            }
        },

        removeTopic(index) {
            this.topics.splice(index, 1);
            this.markDirty();
        },

        get completenessScore() {
            let score = 0;
            if (this.vendorId) score += 20;
            if (this.examCode && this.examCode.trim().length > 0) score += 20;
            if (this.examName && this.examName.trim().length > 0) score += 20;
            const hasPricing = (this.isPdfAvailable && parseFloat(this.pricePdf) > 0) ||
                              (this.isEngineAvailable && parseFloat(this.priceEngine) > 0) ||
                              (this.isBundleAvailable && parseFloat(this.priceBundle) > 0);
            if (hasPricing) score += 30;
            if (parseInt(this.passingScore) > 0) score += 10;
            return Math.min(100, score);
        }
    };
}

function certDropdown(initialCerts, preSelected) {
    return {
        isOpen: false,
        isModalOpen: false,
        isSaving: false,
        search: '',
        errorMessage: '',
        duplicateCert: null,
        toastMessage: '',
        certs: initialCerts,
        selectedIds: preSelected.map(id => parseInt(id)),
        newCert: {
            vendor_id: '',
            name: '',
            code: '',
            description: '',
            is_active: true
        },

        get selectedCerts() {
            return this.selectedIds.map(id => this.certs.find(c => c.id === id)).filter(Boolean);
        },

        get filteredCerts() {
            if (this.search === '') return this.certs;
            const q = this.search.toLowerCase();
            return this.certs.filter(c => 
                c.name.toLowerCase().includes(q) || 
                (c.code && c.code.toLowerCase().includes(q)) ||
                (c.vendor_name && c.vendor_name.toLowerCase().includes(q))
            );
        },

        openDropdown() {
            this.isOpen = true;
            this.$nextTick(() => { if (this.$refs.searchInput) this.$refs.searchInput.focus(); });
        },

        openCreateModal(presetName = '') {
            this.isOpen = false;
            this.errorMessage = '';
            this.duplicateCert = null;
            this.newCert.name = presetName || this.search || '';
            this.newCert.code = '';
            this.newCert.description = '';
            this.newCert.is_active = true;

            const mainVendorSelect = document.getElementById('vendor_id');
            if (mainVendorSelect && mainVendorSelect.value) {
                this.newCert.vendor_id = mainVendorSelect.value;
            } else {
                this.newCert.vendor_id = '';
            }

            this.isModalOpen = true;
        },

        closeCreateModal() {
            this.isModalOpen = false;
            this.errorMessage = '';
            this.duplicateCert = null;
        },

        toggleCert(cert) {
            if (this.selectedIds.includes(cert.id)) {
                this.removeCert(cert.id);
            } else {
                this.selectedIds.push(cert.id);
                this.search = '';
                if (this.$refs.searchInput) this.$refs.searchInput.focus();
            }
        },

        removeCert(id) {
            this.selectedIds = this.selectedIds.filter(selectedId => selectedId !== id);
        },

        isSelected(id) {
            return this.selectedIds.includes(id);
        },

        removeLastIfEmpty() {
            if (this.search === '' && this.selectedIds.length > 0) {
                this.selectedIds.pop();
            }
        },

        useExistingCert(cert) {
            if (!cert) return;
            let existing = this.certs.find(c => c.id === cert.id);
            if (!existing) {
                existing = {
                    id: cert.id,
                    name: cert.name,
                    code: cert.code || '',
                    vendor_name: cert.vendor_name || 'Vendor',
                    vendor_id: cert.vendor_id
                };
                this.certs.push(existing);
            }
            if (!this.selectedIds.includes(cert.id)) {
                this.selectedIds.push(cert.id);
            }
            this.closeCreateModal();
            this.showToast('Existing certification "' + cert.name + '" linked successfully.');
        },

        showToast(msg) {
            this.toastMessage = msg;
            setTimeout(() => {
                this.toastMessage = '';
            }, 4000);
        },

        saveNewCert() {
            if (!this.newCert.name || !this.newCert.name.trim()) {
                this.errorMessage = 'Certification name is required.';
                return;
            }
            if (!this.newCert.vendor_id) {
                this.errorMessage = 'Please select a vendor.';
                return;
            }

            this.isSaving = true;
            this.errorMessage = '';
            this.duplicateCert = null;

            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';

            let payload = {
                vendor_id: this.newCert.vendor_id,
                name: this.newCert.name.trim(),
                code: this.newCert.code ? this.newCert.code.trim() : null,
                description: this.newCert.description ? this.newCert.description.trim() : null,
                is_active: this.newCert.is_active ? 1 : 0
            };

            fetch('{{ route("admin.certifications.store") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify(payload)
            })
            .then(async res => {
                const data = await res.json().catch(() => ({}));
                this.isSaving = false;

                if (res.status === 422 && data.is_duplicate) {
                    this.duplicateCert = data.existing_certification;
                    this.errorMessage = data.message || 'Certification already exists.';
                    return;
                }

                if (res.ok && data.success) {
                    const newC = {
                        id: data.certification.id,
                        name: data.certification.name,
                        code: data.certification.code || '',
                        vendor_name: (data.certification.vendor ? data.certification.vendor.name : '') || 'Vendor',
                        vendor_id: data.certification.vendor_id
                    };
                    this.certs.push(newC);
                    this.selectedIds.push(newC.id);
                    this.closeCreateModal();
                    this.showToast('Certification "' + newC.name + '" created and linked successfully.');
                } else {
                    this.errorMessage = data.message || (data.errors ? Object.values(data.errors).flat().join(' ') : 'Error saving certification.');
                }
            })
            .catch(error => {
                this.isSaving = false;
                this.errorMessage = 'A network error occurred. Please try again.';
                console.error(error);
            });
        }
    };
}
</script>
@endsection