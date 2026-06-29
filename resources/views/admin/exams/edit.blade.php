@extends('layouts.admin')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <div class="flex justify-between items-center">
        <h1 class="text-2xl font-bold text-gray-800">Edit Exam: {{ $exam->exam_code }}</h1>
        <a href="{{ route('admin.exams.index') }}" class="text-sm text-navy hover:underline">
            ← Back to Listing
        </a>
    </div>

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
                        @foreach($vendors as $vendor)
                            <option value="{{ $vendor->id }}" {{ old('vendor_id', $exam->vendor_id) === $vendor->id ? 'selected' : '' }}>
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
            <div>
                <label for="description" class="block text-xs font-bold text-gray-400 uppercase mb-2">Exam Description</label>
                <textarea name="description" id="description" rows="4"
                          class="w-full border-gray-300 rounded text-sm px-3 py-2 focus:border-cyan focus:ring-cyan">{{ old('description', $exam->description) }}</textarea>
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
