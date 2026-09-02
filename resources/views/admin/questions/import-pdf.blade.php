@extends('layouts.admin')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <!-- Header & Submenu Tabs -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Import Questions from PDF</h1>
            <p class="text-xs text-gray-500 mt-1">Upload PDF question banks, certification guides, or practice exam documents for smart extraction.</p>
        </div>
        <div class="flex items-center space-x-2">
            <a href="{{ route('admin.questions.import-form') }}" class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-xs font-bold rounded text-navy bg-white hover:bg-gray-50 transition">
                Import JSON Instead
            </a>
            <a href="{{ route('admin.questions.import-history') }}" class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-xs font-bold rounded text-gray-700 bg-white hover:bg-gray-50 transition">
                View History
            </a>
        </div>
    </div>

    <!-- Navigation Tabs -->
    <div class="border-b border-gray-200">
        <nav class="-mb-px flex space-x-6">
            <a href="{{ route('admin.questions.index') }}" class="border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-3 px-1 border-b-2 font-bold text-xs">
                All Questions
            </a>
            <a href="{{ route('admin.questions.create') }}" class="border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-3 px-1 border-b-2 font-bold text-xs">
                + Add Question
            </a>
            <a href="{{ route('admin.questions.import-form') }}" class="border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-3 px-1 border-b-2 font-bold text-xs">
                Import JSON
            </a>
            <a href="{{ route('admin.questions.import-pdf-form') }}" class="border-orange text-orange whitespace-nowrap py-3 px-1 border-b-2 font-bold text-xs">
                Import PDF
            </a>
            <a href="{{ route('admin.questions.import-history') }}" class="border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-3 px-1 border-b-2 font-bold text-xs">
                Import History
            </a>
        </nav>
    </div>

    @if ($errors->any())
        <div class="bg-red-50 border-l-4 border-red-500 p-4 rounded-r shadow-sm">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-bold text-red-800">PDF Upload Error</h3>
                    <div class="mt-1 text-xs text-red-700">
                        <ul class="list-disc pl-5 space-y-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- PDF Upload & Config Form -->
    <div class="bg-white border border-gray-250 rounded-lg p-6 shadow-sm"
         x-data="{
             selectedFile: null,
             fileName: '',
             fileSize: '',
             isDragging: false,
             topicStrategy: 'auto',
             manualTopic: '',
             processing: false,
             handleDrop(e) {
                 this.isDragging = false;
                 if (e.dataTransfer.files && e.dataTransfer.files.length > 0) {
                     const file = e.dataTransfer.files[0];
                     if (!file.name.toLowerCase().endsWith('.pdf')) {
                         alert('Please select a valid .pdf file.');
                         return;
                     }
                     const fileInput = document.getElementById('pdf_file');
                     if (fileInput) {
                         fileInput.files = e.dataTransfer.files;
                     }
                     this.setFile(file);
                 }
             },
             handleFileSelect(e) {
                 if (e.target.files && e.target.files.length > 0) {
                     this.setFile(e.target.files[0]);
                 }
             },
             setFile(file) {
                 if (!file.name.toLowerCase().endsWith('.pdf')) {
                     alert('Please select a valid .pdf file.');
                     return;
                 }
                 this.selectedFile = file;
                 this.fileName = file.name;
                 this.fileSize = (file.size / (1024 * 1024)).toFixed(2) + ' MB';
             }
         }">
        <form action="{{ route('admin.questions.import-pdf-upload') }}" method="POST" enctype="multipart/form-data"
              @submit="
                  const fileInput = document.getElementById('pdf_file');
                  if (!fileInput || !fileInput.files || fileInput.files.length === 0) {
                      $event.preventDefault();
                      alert('Please select or drop a valid PDF file first.');
                      processing = false;
                      return false;
                  }
                  processing = true;
              "
              class="space-y-8">
            @csrf

            <!-- SECTION A — PDF IMPORT SOURCE -->
            <div class="border-b border-gray-150 pb-6">
                <h3 class="text-sm font-bold text-gray-700 uppercase mb-4">Section A: PDF Document Source</h3>
                
                <div class="mt-2 flex justify-center px-6 pt-5 pb-6 border-2 border-dashed rounded-lg transition"
                     :class="isDragging ? 'border-cyan bg-cyan/5' : 'border-gray-300 hover:border-gray-400'"
                     @dragover.prevent="isDragging = true"
                     @dragleave.prevent="isDragging = false"
                     @drop.prevent="handleDrop($event)">
                    <div class="space-y-3 text-center">
                        <svg class="mx-auto h-12 w-12 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                        </svg>
                        <div class="flex text-sm text-gray-600 justify-center items-center">
                            <label for="pdf_file" class="cursor-pointer bg-white rounded-md font-bold text-cyan hover:text-cyan/80 focus-within:outline-none">
                                <span>Choose PDF File</span>
                            </label>
                            <input id="pdf_file" name="pdf_file" type="file" accept=".pdf,application/pdf" class="hidden" required @change="handleFileSelect($event)">
                            <p class="pl-1">or drag and drop</p>
                        </div>
                        
                        <!-- Feature highlights -->
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-2 pt-2 text-[11px] text-gray-500 font-medium">
                            <div class="flex items-center justify-center space-x-1">
                                <span class="text-green-500">✓</span><span>Text-based PDFs</span>
                            </div>
                            <div class="flex items-center justify-center space-x-1">
                                <span class="text-green-500">✓</span><span>Scanned PDFs with OCR</span>
                            </div>
                            <div class="flex items-center justify-center space-x-1">
                                <span class="text-green-500">✓</span><span>Q&A Detection</span>
                            </div>
                            <div class="flex items-center justify-center space-x-1">
                                <span class="text-green-500">✓</span><span>Exhibit Detection</span>
                            </div>
                            <div class="flex items-center justify-center space-x-1">
                                <span class="text-green-500">✓</span><span>Multi-Page Questions</span>
                            </div>
                            <div class="flex items-center justify-center space-x-1">
                                <span class="text-green-500">✓</span><span>Manual Review First</span>
                            </div>
                        </div>

                        <div x-show="fileName" class="pt-2">
                            <div class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-semibold bg-cyan/10 text-cyan">
                                <svg class="h-4 w-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <span x-text="fileName"></span>&nbsp;(<span x-text="fileSize"></span>)
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- SECTION B — CONFIGURATION & TOPIC STRATEGY -->
            <div class="border-b border-gray-150 pb-6">
                <h3 class="text-sm font-bold text-gray-700 uppercase mb-4">Section B: PDF Import Configuration</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Default Exam -->
                    <div>
                        <label for="default_exam_id" class="block text-xs font-bold text-gray-400 uppercase mb-2">Default Certification Exam</label>
                        <select name="default_exam_id" id="default_exam_id"
                                class="w-full border-gray-300 rounded text-sm px-3 py-2 focus:border-cyan focus:ring-cyan">
                            <option value="">-- Select Exam (Fallback) --</option>
                            @foreach($exams as $exam)
                                <option value="{{ $exam->id }}" {{ old('default_exam_id', request('exam_id')) == $exam->id ? 'selected' : '' }}>
                                    {{ $exam->exam_code }} - {{ $exam->exam_name }}
                                </option>
                            @endforeach
                        </select>
                        <p class="text-[11px] text-gray-400 mt-1">Used if the document does not mention an exam code.</p>
                    </div>

                    <!-- Topic Strategy -->
                    <div>
                        <label class="block text-xs font-bold text-gray-400 uppercase mb-2">Topic / Chapter Strategy</label>
                        <div class="space-y-2">
                            <label class="flex items-center space-x-2 text-xs text-gray-700 cursor-pointer">
                                <input type="radio" name="topic_strategy" value="auto" x-model="topicStrategy" class="text-cyan focus:ring-cyan">
                                <span class="font-bold">Automatically detect chapters & headings</span>
                            </label>
                            <label class="flex items-center space-x-2 text-xs text-gray-700 cursor-pointer">
                                <input type="radio" name="topic_strategy" value="manual" x-model="topicStrategy" class="text-cyan focus:ring-cyan">
                                <span>Use specific topic for all questions</span>
                            </label>
                        </div>

                        <div x-show="topicStrategy === 'manual'" class="mt-2" style="display: none;">
                            <input type="text" name="manual_topic" x-model="manualTopic" placeholder="e.g. Identity and Access Management"
                                   class="w-full border-gray-300 rounded text-xs px-3 py-1.5 focus:border-cyan focus:ring-cyan">
                        </div>
                    </div>
                </div>
            </div>

            <!-- SECTION C — PDF EXTRACTION PIPELINE OPTIONS -->
            <div class="border-b border-gray-150 pb-6">
                <h3 class="text-sm font-bold text-gray-700 uppercase mb-4">Section C: Extraction Pipeline Options</h3>
                
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4 text-xs">
                    <label class="flex items-start space-x-2 p-2.5 border border-gray-200 rounded hover:bg-gray-50 cursor-pointer">
                        <input type="checkbox" name="opt_extract_text" value="1" checked class="rounded border-gray-300 text-cyan focus:ring-cyan h-4 w-4">
                        <span><strong>Extract Text:</strong> Native PDF streams first</span>
                    </label>

                    <label class="flex items-start space-x-2 p-2.5 border border-gray-200 rounded hover:bg-gray-50 cursor-pointer">
                        <input type="checkbox" name="opt_detect_questions" value="1" checked class="rounded border-gray-300 text-cyan focus:ring-cyan h-4 w-4">
                        <span><strong>Detect Questions:</strong> Numbered & labeled headers</span>
                    </label>

                    <label class="flex items-start space-x-2 p-2.5 border border-gray-200 rounded hover:bg-gray-50 cursor-pointer">
                        <input type="checkbox" name="opt_detect_options" value="1" checked class="rounded border-gray-300 text-cyan focus:ring-cyan h-4 w-4">
                        <span><strong>Detect Options:</strong> A..D choice boundaries</span>
                    </label>

                    <label class="flex items-start space-x-2 p-2.5 border border-gray-200 rounded hover:bg-gray-50 cursor-pointer">
                        <input type="checkbox" name="opt_detect_answers" value="1" checked class="rounded border-gray-300 text-cyan focus:ring-cyan h-4 w-4">
                        <span><strong>Detect Correct Answers:</strong> Answer key parsing</span>
                    </label>

                    <label class="flex items-start space-x-2 p-2.5 border border-gray-200 rounded hover:bg-gray-50 cursor-pointer">
                        <input type="checkbox" name="opt_detect_explanations" value="1" checked class="rounded border-gray-300 text-cyan focus:ring-cyan h-4 w-4">
                        <span><strong>Detect Explanations:</strong> Rationale & notes</span>
                    </label>

                    <label class="flex items-start space-x-2 p-2.5 border border-gray-200 rounded hover:bg-gray-50 cursor-pointer">
                        <input type="checkbox" name="opt_detect_references" value="1" checked class="rounded border-gray-300 text-cyan focus:ring-cyan h-4 w-4">
                        <span><strong>Detect References:</strong> URLs & documentation</span>
                    </label>

                    <label class="flex items-start space-x-2 p-2.5 border border-gray-200 rounded hover:bg-gray-50 cursor-pointer">
                        <input type="checkbox" name="opt_extract_images" value="1" checked class="rounded border-gray-300 text-cyan focus:ring-cyan h-4 w-4">
                        <span><strong>Extract Exhibits:</strong> Image attachments</span>
                    </label>

                    <label class="flex items-start space-x-2 p-2.5 border border-gray-200 rounded hover:bg-gray-50 cursor-pointer">
                        <input type="checkbox" name="opt_run_ocr" value="1" checked class="rounded border-gray-300 text-cyan focus:ring-cyan h-4 w-4">
                        <span><strong>Run OCR:</strong> On scanned image pages</span>
                    </label>

                    <label class="flex items-start space-x-2 p-2.5 border border-gray-200 rounded hover:bg-gray-50 cursor-pointer">
                        <input type="checkbox" name="opt_detect_duplicates" value="1" checked class="rounded border-gray-300 text-cyan focus:ring-cyan h-4 w-4">
                        <span><strong>Detect Duplicates:</strong> Against question bank</span>
                    </label>
                </div>
            </div>

            <!-- Submit Button -->
            <div class="pt-2 flex justify-end space-x-3">
                <a href="{{ route('admin.questions.index') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-600 text-xs font-bold py-2.5 px-6 rounded transition">
                    Cancel
                </a>
                <button type="submit" :disabled="processing"
                        class="bg-navy hover:bg-opacity-95 text-white text-xs font-bold py-2.5 px-6 rounded shadow transition disabled:opacity-50 flex items-center space-x-2">
                    <span x-text="processing ? 'Extracting & Parsing PDF...' : 'Extract & Review Questions'"></span>
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
