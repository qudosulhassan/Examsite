@extends('layouts.admin')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <div class="flex justify-between items-center">
        <h1 class="text-2xl font-bold text-gray-800">Edit Exam: {{ $exam->exam_code }}</h1>
        <a href="{{ route('admin.exams.index') }}" class="text-sm text-navy hover:underline">
            ← Back to Listing
        </a>
    </div>

    @php
    $certData = $certifications->map(function($c) {
        return [
            'id' => $c->id,
            'name' => $c->name,
            'vendor_name' => $c->vendor ? $c->vendor->name : 'Unknown',
            'vendor_id' => $c->vendor_id
        ];
    })->values()->toJson();

    $preSelected = $exam->certifications->pluck('id')->toJson();
    @endphp

    <!-- Edit Form -->
    <div class="bg-white border border-gray-250 rounded-lg p-6 shadow-sm">
        <form action="{{ route('admin.exams.update', $exam->id) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf
            @method('PUT')
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Vendor Select -->
                <div>
                    <label for="vendor_id" class="block text-xs font-bold text-gray-400 uppercase mb-2">Certification Vendor</label>
                    <select name="vendor_id" id="vendor_id" required
                            class="w-full border-gray-300 rounded text-sm px-3 py-2 focus:border-cyan focus:ring-cyan">
                        <option value="">Select a vendor...</option>
                        @foreach($vendors as $vendor)
                            <option value="{{ $vendor->id }}" {{ old('vendor_id', $exam->vendor_id) == $vendor->id ? 'selected' : '' }}>
                                {{ $vendor->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('vendor_id')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Exam Code -->
                <div>
                    <label for="exam_code" class="block text-xs font-bold text-gray-400 uppercase mb-2">Exam Code</label>
                    <input type="text" name="exam_code" id="exam_code" required value="{{ old('exam_code', $exam->exam_code) }}"
                           class="w-full border-gray-300 rounded text-sm px-3 py-2 focus:border-cyan focus:ring-cyan">
                    @error('exam_code')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Custom Certifications Multi-Select (AlpineJS) -->
            <div x-data="certDropdown({{ $certData }}, {{ $preSelected }})" class="relative">
                <label class="block text-xs font-bold text-gray-400 uppercase mb-2">Certifications</label>
                
                <!-- Main Input Area (Click to open) -->
                <div @click="openDropdown()" class="min-h-[42px] w-full border border-gray-300 rounded text-sm p-1.5 focus-within:border-cyan focus-within:ring-1 focus-within:ring-cyan bg-white flex flex-wrap gap-1.5 items-center cursor-text transition-colors shadow-sm">
                    <!-- Selected Badges -->
                    <template x-for="cert in selectedCerts" :key="cert.id">
                        <span class="bg-gray-100 text-navy text-xs font-bold px-2 py-1 rounded flex items-center gap-1 border border-gray-300 shadow-sm">
                            <span x-text="cert.name"></span>
                            <button type="button" @click.stop="removeCert(cert.id)" class="text-gray-500 hover:text-red-600 focus:outline-none ml-1 transition-colors">
                                <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                            </button>
                            <!-- Hidden input to submit with the form -->
                            <input type="hidden" name="certifications[]" :value="cert.id">
                        </span>
                    </template>
                    <!-- Search Input -->
                    <input type="text" x-model="search" x-ref="searchInput" @keydown.backspace="removeLastIfEmpty()" class="flex-grow border-0 focus:ring-0 text-sm p-0.5 min-w-[120px] outline-none placeholder-gray-400" placeholder="Search and select certifications...">
                </div>

                <!-- Dropdown Panel -->
                <div x-show="isOpen" @click.away="isOpen = false" x-transition.opacity.duration.200ms class="absolute z-50 mt-1 w-full bg-white border border-gray-200 rounded-lg shadow-xl overflow-hidden" style="display: none;">
                    
                    <!-- View: List -->
                    <div x-show="!isCreating" class="flex flex-col max-h-72">
                        <ul class="overflow-y-auto flex-grow py-1">
                            <template x-for="cert in filteredCerts" :key="cert.id">
                                <li @click="toggleCert(cert)" class="px-4 py-2.5 hover:bg-cyan hover:bg-opacity-10 cursor-pointer flex items-center justify-between transition-colors border-b border-gray-50 last:border-0">
                                    <div>
                                        <div x-text="cert.name" class="text-sm font-bold text-gray-800"></div>
                                        <div x-text="cert.vendor_name" class="text-[10px] uppercase font-bold text-gray-400 mt-0.5"></div>
                                    </div>
                                    <!-- Checkmark if selected -->
                                    <div x-show="isSelected(cert.id)" class="text-cyan">
                                        <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" /></svg>
                                    </div>
                                </li>
                            </template>
                            <!-- Empty State -->
                            <li x-show="filteredCerts.length === 0" class="px-4 py-6 text-sm text-gray-500 text-center flex flex-col items-center">
                                <svg class="h-8 w-8 text-gray-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                <span>No certifications found for "<span x-text="search" class="font-bold"></span>".</span>
                            </li>
                        </ul>
                        <!-- Sticky Bottom Action -->
                        <div class="border-t border-gray-150 p-2 bg-gray-50">
                            <button type="button" @click.stop="openCreateForm()" class="w-full text-left text-sm font-bold text-cyan hover:text-navy px-3 py-2 flex items-center transition-colors rounded hover:bg-white">
                                <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                                Create New Certification
                            </button>
                        </div>
                    </div>

                    <!-- View: Create New -->
                    <div x-show="isCreating" class="p-5 bg-gray-50">
                        <div class="flex items-center justify-between mb-4">
                            <h4 class="text-sm font-bold text-navy">Add New Certification</h4>
                            <button type="button" @click="isCreating = false" class="text-gray-400 hover:text-gray-600 focus:outline-none">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                            </button>
                        </div>
                        
                        <div x-show="errorMessage" x-text="errorMessage" class="bg-red-50 text-red-600 border border-red-100 p-2.5 rounded text-xs mb-4 font-bold"></div>
                        
                        <div class="space-y-4">
                            <div>
                                <label class="block text-[10px] font-bold text-gray-500 uppercase mb-1">Vendor</label>
                                <select x-model="newCert.vendor_id" class="w-full border-gray-300 rounded text-sm p-2 focus:ring-cyan focus:border-cyan shadow-sm bg-white">
                                    <option value="">Select Vendor...</option>
                                    @foreach($vendors as $vendor)
                                        <option value="{{ $vendor->id }}">{{ $vendor->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold text-gray-500 uppercase mb-1">Certification Name</label>
                                <input type="text" x-model="newCert.name" class="w-full border-gray-300 rounded text-sm p-2 focus:ring-cyan focus:border-cyan shadow-sm bg-white" placeholder="e.g. AWS Certified Solutions Architect">
                            </div>
                            <div class="flex items-center space-x-3 pt-2">
                                <button type="button" @click="saveNewCert()" class="flex-1 bg-cyan text-navy font-bold py-2.5 px-4 rounded text-xs shadow-sm hover:bg-opacity-90 transition-all flex justify-center items-center">
                                    <span x-show="isSaving" class="flex items-center">
                                        <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-navy" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                        Saving...
                                    </span>
                                    <span x-show="!isSaving">Save & Select</span>
                                </button>
                                <button type="button" @click="isCreating = false" class="flex-1 bg-white border border-gray-300 text-gray-700 font-bold py-2.5 px-4 rounded text-xs shadow-sm hover:bg-gray-50 transition-all">
                                    Cancel
                                </button>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            <!-- Exam Name -->
            <div>
                <label for="exam_name" class="block text-xs font-bold text-gray-400 uppercase mb-2">Exam Name</label>
                <input type="text" name="exam_name" id="exam_name" required value="{{ old('exam_name', $exam->exam_name) }}"
                       class="w-full border-gray-300 rounded text-sm px-3 py-2 focus:border-cyan focus:ring-cyan">
                @error('exam_name')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Price PDF -->
                <div>
                    <label for="price_pdf" class="block text-xs font-bold text-gray-400 uppercase mb-2">PDF Price ($)</label>
                    <input type="number" step="0.01" name="price_pdf" id="price_pdf" required value="{{ old('price_pdf', $exam->price_pdf) }}"
                           class="w-full border-gray-300 rounded text-sm px-3 py-2 focus:border-cyan focus:ring-cyan">
                    @error('price_pdf')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Price Engine -->
                <div>
                    <label for="price_engine" class="block text-xs font-bold text-gray-400 uppercase mb-2">Simulator Price ($)</label>
                    <input type="number" step="0.01" name="price_engine" id="price_engine" required value="{{ old('price_engine', $exam->price_engine) }}"
                           class="w-full border-gray-300 rounded text-sm px-3 py-2 focus:border-cyan focus:ring-cyan">
                    @error('price_engine')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Passing Score -->
                <div>
                    <label for="passing_score" class="block text-xs font-bold text-gray-400 uppercase mb-2">Passing Score (%)</label>
                    <input type="number" name="passing_score" id="passing_score" required value="{{ old('passing_score', $exam->passing_score) }}" min="50" max="100"
                           class="w-full border-gray-300 rounded text-sm px-3 py-2 focus:border-cyan focus:ring-cyan">
                    @error('passing_score')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <h3 class="text-md font-bold text-navy mt-6 mb-4 border-b pb-2">Update Prices (Optional)</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                <!-- 3 Months Update -->
                <div>
                    <label for="update_price_3_months" class="block text-xs font-bold text-gray-400 uppercase mb-2">3 Months Update ($)</label>
                    <input type="number" step="0.01" name="update_price_3_months" id="update_price_3_months" value="{{ old('update_price_3_months', $exam->update_price_3_months) }}"
                           class="w-full border-gray-300 rounded text-sm px-3 py-2 focus:border-cyan focus:ring-cyan">
                    <p class="text-[10px] text-gray-400 mt-1">Default 0 (Included)</p>
                    @error('update_price_3_months')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- 6 Months Update -->
                <div>
                    <label for="update_price_6_months" class="block text-xs font-bold text-gray-400 uppercase mb-2">6 Months Update ($)</label>
                    <input type="number" step="0.01" name="update_price_6_months" id="update_price_6_months" value="{{ old('update_price_6_months', $exam->update_price_6_months) }}"
                           class="w-full border-gray-300 rounded text-sm px-3 py-2 focus:border-cyan focus:ring-cyan">
                    @error('update_price_6_months')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- 12 Months Update -->
                <div>
                    <label for="update_price_12_months" class="block text-xs font-bold text-gray-400 uppercase mb-2">12 Months Update ($)</label>
                    <input type="number" step="0.01" name="update_price_12_months" id="update_price_12_months" value="{{ old('update_price_12_months', $exam->update_price_12_months) }}"
                           class="w-full border-gray-300 rounded text-sm px-3 py-2 focus:border-cyan focus:ring-cyan">
                    @error('update_price_12_months')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Difficulty -->
                <div>
                    <label for="difficulty" class="block text-xs font-bold text-gray-400 uppercase mb-2">Difficulty</label>
                    <select name="difficulty" id="difficulty" required
                            class="w-full border-gray-300 rounded text-sm px-3 py-2 focus:border-cyan focus:ring-cyan">
                        @foreach(['Associate', 'Professional', 'Expert'] as $diff)
                            <option value="{{ $diff }}" {{ old('difficulty', $exam->difficulty) === $diff ? 'selected' : '' }}>{{ $diff }}</option>
                        @endforeach
                    </select>
                    @error('difficulty')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Exam Type -->
                <div>
                    <label for="exam_type" class="block text-xs font-bold text-gray-400 uppercase mb-2">Questions Format</label>
                    <select name="exam_type" id="exam_type" required
                            class="w-full border-gray-300 rounded text-sm px-3 py-2 focus:border-cyan focus:ring-cyan">
                        @foreach(['MultipleChoice' => 'Multiple Choice', 'MultiSelect' => 'Multi-Select Checkboxes', 'LabBased' => 'Lab-Based Case Studies'] as $val => $lbl)
                            <option value="{{ $val }}" {{ old('exam_type', $exam->exam_type) === $val ? 'selected' : '' }}>{{ $lbl }}</option>
                        @endforeach
                    </select>
                    @error('exam_type')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Topics -->
            <div>
                <label for="topics" class="block text-xs font-bold text-gray-400 uppercase mb-2">Exam Syllabus Topics (Comma-separated)</label>
                <input type="text" name="topics" id="topics" value="{{ old('topics', implode(', ', $exam->topics ?: [])) }}"
                       class="w-full border-gray-300 rounded text-sm px-3 py-2 focus:border-cyan focus:ring-cyan">
                <p class="text-[10px] text-gray-400 mt-1">Separate syllabus chapters/topics with a comma.</p>
                @error('topics')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Description -->
            <div class="mb-6 tiptap-container" data-content="{{ base64_encode(old('description', $exam->description)) }}">
                <label class="block text-xs font-bold text-gray-400 uppercase mb-2">Exam Description</label>
                
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
                <div class="editor-element w-full border border-gray-300 rounded-b-md min-h-[150px] p-4 focus-within:border-cyan focus-within:ring-1 focus-within:ring-cyan bg-white prose max-w-none text-sm"></div>
                
                <!-- Hidden Input for Form Submission -->
                <input type="hidden" name="description" class="content-input" value="{{ old('description', $exam->description) }}">

                @error('description')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Demo PDF File -->
                <div>
                    <label for="demo_pdf" class="block text-xs font-bold text-gray-400 uppercase mb-2">Upload Demo PDF File</label>
                    <input type="file" name="demo_pdf" id="demo_pdf" accept=".pdf"
                           class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:text-xs file:font-semibold file:bg-gray-100 file:text-navy hover:file:bg-gray-200">
                    @if($exam->demo_pdf_filename)
                        <p class="text-[10px] text-green-600 mt-1 font-semibold">Current file: {{ $exam->demo_pdf_filename }}</p>
                    @else
                        <p class="text-[10px] text-gray-400 mt-1">Select the partial practice PDF guide to send as free samples.</p>
                    @endif
                    @error('demo_pdf')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Full Access File -->
                <div>
                    <label for="full_pdf" class="block text-xs font-bold text-gray-400 uppercase mb-2">Upload Full Access PDF File</label>
                    <input type="file" name="full_pdf" id="full_pdf" accept=".pdf"
                           class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:text-xs file:font-semibold file:bg-gray-100 file:text-navy hover:file:bg-gray-200">
                    @if($exam->full_pdf_filename)
                        <p class="text-[10px] text-green-600 mt-1 font-semibold">Current file: {{ $exam->full_pdf_filename }}</p>
                    @else
                        <p class="text-[10px] text-gray-400 mt-1">Select the complete study guide PDF that customers purchase.</p>
                    @endif
                    @error('full_pdf')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Is Active Checkbox -->
            <div class="flex items-center mb-8">
                <input type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', $exam->is_active) ? 'checked' : '' }}
                       class="rounded border-gray-300 text-cyan focus:ring-cyan h-4 w-4">
                <label for="is_active" class="ml-2 text-sm font-semibold text-gray-700">Mark as Active (Show in search and pricing lists)</label>
            </div>

            <!-- SEO Configuration Card -->
            <div class="bg-gray-50 -mx-8 px-8 py-6 border-t border-b border-gray-150 mb-8">
                <h3 class="text-sm font-extrabold text-navy uppercase mb-4 flex items-center">
                    <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    Search Engine Optimization (SEO)
                </h3>
                <p class="text-xs text-gray-500 mb-6">Leave these blank to automatically generate them based on the exam name and description.</p>
                
                <div class="space-y-4">
                    <div>
                        <label for="meta_title" class="block text-xs font-bold text-gray-400 uppercase mb-2">Meta Title</label>
                        <input type="text" name="meta_title" id="meta_title" value="{{ old('meta_title', $exam->meta_title) }}" placeholder="e.g., Best AWS Certified Cloud Practitioner Practice Exams"
                               class="w-full bg-white border border-gray-200 rounded px-3 py-2 text-sm text-gray-700 focus:outline-none focus:border-cyan focus:ring-1 focus:ring-cyan">
                        @error('meta_title') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="meta_description" class="block text-xs font-bold text-gray-400 uppercase mb-2">Meta Description (Max 160 chars)</label>
                        <textarea name="meta_description" id="meta_description" rows="2" placeholder="Write a compelling description for Google search results..."
                                  class="w-full bg-white border border-gray-200 rounded px-3 py-2 text-sm text-gray-700 focus:outline-none focus:border-cyan focus:ring-1 focus:ring-cyan">{{ old('meta_description', $exam->meta_description) }}</textarea>
                        @error('meta_description') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="meta_keywords" class="block text-xs font-bold text-gray-400 uppercase mb-2">Meta Keywords (Comma-separated)</label>
                        <input type="text" name="meta_keywords" id="meta_keywords" value="{{ old('meta_keywords', $exam->meta_keywords) }}" placeholder="e.g., aws exam dumps, clf-c02 practice test, cloud practitioner"
                               class="w-full bg-white border border-gray-200 rounded px-3 py-2 text-sm text-gray-700 focus:outline-none focus:border-cyan focus:ring-1 focus:ring-cyan">
                        @error('meta_keywords') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            <!-- Submit Buttons -->
            <div class="border-t border-gray-150 pt-6 flex justify-end space-x-3">
                <a href="{{ route('admin.exams.index') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-600 text-xs font-bold py-2.5 px-6 rounded transition">
                    Cancel
                </a>
                <button type="submit" class="bg-navy hover:bg-opacity-95 text-white text-xs font-bold py-2.5 px-6 rounded shadow transition">
                    Update Exam
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
function certDropdown(initialCerts, preSelected) {
    return {
        isOpen: false,
        isCreating: false,
        isSaving: false,
        search: '',
        errorMessage: '',
        certs: initialCerts,
        selectedIds: preSelected.map(id => parseInt(id)),
        newCert: {
            vendor_id: '',
            name: ''
        },

        get selectedCerts() {
            return this.selectedIds.map(id => this.certs.find(c => c.id === id)).filter(Boolean);
        },

        get filteredCerts() {
            if (this.search === '') return this.certs;
            const q = this.search.toLowerCase();
            return this.certs.filter(c => 
                c.name.toLowerCase().includes(q) || 
                c.vendor_name.toLowerCase().includes(q)
            );
        },

        openDropdown() {
            this.isOpen = true;
            this.isCreating = false;
            this.$nextTick(() => { this.$refs.searchInput.focus() });
        },

        openCreateForm() {
            this.isCreating = true;
            this.errorMessage = '';
            this.newCert.name = this.search; // Pre-fill with what they searched for
            
            // Try to auto-select vendor based on main form vendor
            const mainVendorSelect = document.getElementById('vendor_id');
            if (mainVendorSelect && mainVendorSelect.value) {
                this.newCert.vendor_id = mainVendorSelect.value;
            } else {
                this.newCert.vendor_id = '';
            }
        },

        toggleCert(cert) {
            if (this.selectedIds.includes(cert.id)) {
                this.removeCert(cert.id);
            } else {
                this.selectedIds.push(cert.id);
                this.search = '';
                this.$refs.searchInput.focus();
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
        },        saveNewCert() {
            if (!this.newCert.name || !this.newCert.vendor_id) {
                this.errorMessage = 'Vendor and Name are required.';
                return;
            }

            this.isSaving = true;
            this.errorMessage = '';

            let payload = {
                vendor_id: this.newCert.vendor_id,
                name: this.newCert.name,
                slug: this.newCert.name.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/(^-|-$)+/g, ''),
                is_active: 1,
                _token: document.querySelector('meta[name="csrf-token"]').content
            };

            fetch('{{ route("admin.certifications.store") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify(payload)
            })
            .then(res => res.json())
            .then(data => {
                this.isSaving = false;
                if (data.success) {
                    const newC = {
                        id: data.certification.id,
                        name: data.certification.name,
                        vendor_name: data.certification.vendor ? data.certification.vendor.name : 'Unknown',
                        vendor_id: data.certification.vendor_id
                    };
                    this.certs.push(newC);
                    this.selectedIds.push(newC.id);
                    this.isCreating = false;
                    this.newCert.name = '';
                    this.search = '';
                    this.$refs.searchInput.focus();
                } else {
                    this.errorMessage = data.message || 'Error saving certification.';
                }
            })
            .catch(error => {
                this.isSaving = false;
                this.errorMessage = 'Network error occurred.';
                console.error(error);
            });
        }
    }
}

document.addEventListener('DOMContentLoaded', function() {
    if (window.ClassicEditor) {
        window.ClassicEditor
            .create(document.querySelector('#description'), {
                toolbar: [ 'heading', '|', 'bold', 'italic', 'link', 'bulletedList', 'numberedList', 'blockQuote', '|', 'undo', 'redo' ]
            })
            .then(editor => {
                // Style the container to look substantial
                editor.editing.view.change(writer => {
                    writer.setStyle('min-height', '200px', editor.editing.view.document.getRoot());
                });
            })
            .catch(error => {
                console.error('CKEditor initialization failed:', error);
            });
    } else {
        console.error("CKEditor failed to load from local bundle.");
    }
});
</script>
@endsection
