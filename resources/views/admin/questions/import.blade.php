@extends('layouts.admin')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <!-- Header & Submenu Tabs -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Import Questions</h1>
            <p class="text-xs text-gray-500 mt-1">Bulk Question Importer &bull; Upload and validate question banks in bulk using Universal V2 or Legacy V1 formats.</p>
        </div>
        <div class="flex items-center space-x-2">
            <a href="{{ route('admin.questions.import-sample') }}" class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-xs font-bold rounded text-navy bg-white hover:bg-gray-50 transition">
                <svg class="h-4 w-4 mr-1.5 text-cyan" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
                Download Sample JSON
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
            <a href="{{ route('admin.questions.import-form') }}" class="border-orange text-orange whitespace-nowrap py-3 px-1 border-b-2 font-bold text-xs">
                Import JSON
            </a>
            <a href="{{ route('admin.questions.import-pdf-form') }}" class="border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-3 px-1 border-b-2 font-bold text-xs">
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
                    <h3 class="text-sm font-bold text-red-800">Upload Validation Error</h3>
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

    <!-- Import Form -->
    <div class="bg-white border border-gray-250 rounded-lg p-6 shadow-sm"
         x-data="{
             selectedFile: null,
             fileName: '',
             fileSize: '',
             isDragging: false,
             uploading: false,
             handleDrop(e) {
                 this.isDragging = false;
                 if (e.dataTransfer.files && e.dataTransfer.files.length > 0) {
                     const file = e.dataTransfer.files[0];
                     if (!file.name.endsWith('.json') && !file.name.endsWith('.txt')) {
                         alert('Please select a valid .json file.');
                         return;
                     }
                     const fileInput = document.getElementById('json_file');
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
                 if (!file.name.endsWith('.json') && !file.name.endsWith('.txt')) {
                     alert('Please select a valid .json file.');
                     return;
                 }
                 this.selectedFile = file;
                 this.fileName = file.name;
                 this.fileSize = (file.size / 1024).toFixed(1) + ' KB';
             }
         }">
        <form action="{{ route('admin.questions.import-upload') }}" method="POST" enctype="multipart/form-data" @submit="uploading = true" class="space-y-8">
            @csrf

            <!-- SECTION A — IMPORT SOURCE -->
            <div class="border-b border-gray-150 pb-6">
                <h3 class="text-sm font-bold text-gray-700 uppercase mb-4">Section A: Import Source</h3>
                
                <div class="mt-2 flex justify-center px-6 pt-5 pb-6 border-2 border-dashed rounded-lg transition"
                     :class="isDragging ? 'border-cyan bg-cyan/5' : 'border-gray-300 hover:border-gray-400'"
                     @dragover.prevent="isDragging = true"
                     @dragleave.prevent="isDragging = false"
                     @drop.prevent="handleDrop($event)">
                    <div class="space-y-2 text-center">
                        <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                            <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                        <div class="flex text-sm text-gray-600 justify-center">
                            <label for="json_file" class="relative cursor-pointer bg-white rounded-md font-bold text-cyan hover:text-cyan/80 focus-within:outline-none">
                                <span>Upload JSON File</span>
                                <input id="json_file" name="json_file" type="file" accept=".json,.txt" class="sr-only" required @change="handleFileSelect($event)">
                            </label>
                            <p class="pl-1">or drag and drop</p>
                        </div>
                        <p class="text-xs text-gray-500">
                            Supported Formats: <strong>Universal Question JSON V2</strong>, <strong>Legacy JSON V1</strong>, or <strong>Mixed</strong>
                        </p>
                        <p class="text-[11px] text-gray-400">Maximum File Size: 50 MB</p>

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

            <!-- SECTION B — DEFAULT EXAM ASSIGNMENT -->
            <div class="border-b border-gray-150 pb-6">
                <h3 class="text-sm font-bold text-gray-700 uppercase mb-4">Section B: Default Exam Assignment</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 items-start">
                    <div>
                        <label for="default_exam_id" class="block text-xs font-bold text-gray-400 uppercase mb-2">Default Certification Exam</label>
                        <select name="default_exam_id" id="default_exam_id"
                                class="w-full border-gray-300 rounded text-sm px-3 py-2 focus:border-cyan focus:ring-cyan">
                            <option value="">-- Optional (If not provided in JSON) --</option>
                            @foreach($exams as $exam)
                                <option value="{{ $exam->id }}" {{ old('default_exam_id', request('exam_id')) == $exam->id ? 'selected' : '' }}>
                                    {{ $exam->exam_code }} - {{ $exam->exam_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="bg-gray-50 p-4 rounded-lg border border-gray-200 text-xs space-y-2">
                        <div class="font-bold text-gray-700 uppercase tracking-wide">Exam Resolution Priority:</div>
                        <ol class="list-decimal pl-4 space-y-1 text-gray-600">
                            <li>Question Item <code>exam_id</code> (Direct database ID)</li>
                            <li>Question Item <code>exam_code</code> (e.g. <code>AZ-900</code>)</li>
                            <li>Selected Default Exam (from dropdown)</li>
                            <li>Validation Error (if unresolvable)</li>
                        </ol>
                    </div>
                </div>
            </div>

            <!-- SECTION C — IMPORT OPTIONS -->
            <div class="border-b border-gray-150 pb-6">
                <h3 class="text-sm font-bold text-gray-700 uppercase mb-4">Section C: Import Options</h3>
                
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <label class="flex items-start space-x-3 p-3 border border-gray-200 rounded-lg hover:bg-gray-50 cursor-pointer">
                        <input type="checkbox" name="opt_normalize_legacy" value="1" checked class="mt-0.5 rounded border-gray-300 text-cyan focus:ring-cyan h-4 w-4">
                        <div class="text-xs">
                            <span class="font-bold text-gray-800 block">Normalize Legacy Questions</span>
                            <span class="text-gray-500">Auto-convert option_a..d and correct_option to Universal V2 structure.</span>
                        </div>
                    </label>

                    <label class="flex items-start space-x-3 p-3 border border-gray-200 rounded-lg hover:bg-gray-50 cursor-pointer">
                        <input type="checkbox" name="opt_detect_duplicates" value="1" checked class="mt-0.5 rounded border-gray-300 text-cyan focus:ring-cyan h-4 w-4">
                        <div class="text-xs">
                            <span class="font-bold text-gray-800 block">Detect Duplicates</span>
                            <span class="text-gray-500">Run hash matching & similarity analysis against existing question bank.</span>
                        </div>
                    </label>

                    <label class="flex items-start space-x-3 p-3 border border-gray-200 rounded-lg hover:bg-gray-50 cursor-pointer">
                        <input type="checkbox" name="opt_run_validation" value="1" checked class="mt-0.5 rounded border-gray-300 text-cyan focus:ring-cyan h-4 w-4">
                        <div class="text-xs">
                            <span class="font-bold text-gray-800 block">Run Full Validation</span>
                            <span class="text-gray-500">Assert option existence, type constraints, and reference URLs.</span>
                        </div>
                    </label>

                    <label class="flex items-start space-x-3 p-3 border border-gray-200 rounded-lg hover:bg-gray-50 cursor-pointer">
                        <input type="checkbox" name="opt_import_as_draft" value="1" checked class="mt-0.5 rounded border-gray-300 text-cyan focus:ring-cyan h-4 w-4">
                        <div class="text-xs">
                            <span class="font-bold text-gray-800 block">Import as Draft</span>
                            <span class="text-gray-500">Save newly imported questions as inactive drafts for review safety.</span>
                        </div>
                    </label>

                    <label class="flex items-start space-x-3 p-3 border border-gray-200 rounded-lg hover:bg-gray-50 cursor-pointer sm:col-span-2">
                        <input type="checkbox" name="opt_require_review" value="1" checked class="mt-0.5 rounded border-gray-300 text-cyan focus:ring-cyan h-4 w-4">
                        <div class="text-xs">
                            <span class="font-bold text-gray-800 block">Require Review Before Committing</span>
                            <span class="text-gray-500">Open interactive review grid to edit, approve, or reject items before DB commit.</span>
                        </div>
                    </label>
                </div>
            </div>

            <!-- Submit Button -->
            <div class="pt-2 flex justify-end space-x-3">
                <a href="{{ route('admin.questions.index') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-600 text-xs font-bold py-2.5 px-6 rounded transition">
                    Cancel
                </a>
                <button type="submit" :disabled="uploading"
                        class="bg-navy hover:bg-opacity-95 text-white text-xs font-bold py-2.5 px-6 rounded shadow transition disabled:opacity-50 flex items-center space-x-2">
                    <span x-text="uploading ? 'Processing & Validating...' : 'Upload & Validate JSON'"></span>
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
