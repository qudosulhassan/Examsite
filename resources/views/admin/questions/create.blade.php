@extends('layouts.admin')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <div class="flex justify-between items-center">
        <h1 class="text-2xl font-bold text-gray-800">Add Exam Question</h1>
        <a href="{{ route('admin.questions.index') }}" class="text-sm text-navy hover:underline">
            ← Back to Listing
        </a>
    </div>

    <!-- Create Form -->
    <div class="bg-white border border-gray-250 rounded-lg p-6 shadow-sm">
        <form action="{{ route('admin.questions.store') }}" method="POST" class="space-y-6">
            @csrf
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Exam Select -->
                <div>
                    <label for="exam_id" class="block text-xs font-bold text-gray-400 uppercase mb-2">Certification Exam</label>
                    <select name="exam_id" id="exam_id" required
                            class="w-full border-gray-300 rounded text-sm px-3 py-2 focus:border-cyan focus:ring-cyan">
                        <option value="">Select an Exam</option>
                        @foreach($exams as $exam)
                            <option value="{{ $exam->id }}">{{ $exam->exam_code }} - {{ $exam->exam_name }}</option>
                        @endforeach
                    </select>
                    @error('exam_id')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Syllabus Topic -->
                <div>
                    <label for="topic" class="block text-xs font-bold text-gray-400 uppercase mb-2">Syllabus Topic / Chapter Name</label>
                    <input type="text" name="topic" id="topic" placeholder="e.g. Identity and Access Management"
                           class="w-full border-gray-300 rounded text-sm px-3 py-2 focus:border-cyan focus:ring-cyan">
                    @error('topic')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Question Text -->
            <div>
                <label for="question_text" class="block text-xs font-bold text-gray-400 uppercase mb-2">Question Text</label>
                <textarea name="question_text" id="question_text" rows="4" required placeholder="Enter question description..."
                          class="w-full border-gray-300 rounded text-sm px-3 py-2 focus:border-cyan focus:ring-cyan"></textarea>
                @error('question_text')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Options Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Option A -->
                <div>
                    <label for="option_a" class="block text-xs font-bold text-gray-400 uppercase mb-2">Option A</label>
                    <input type="text" name="option_a" id="option_a" required placeholder="Option A choice"
                           class="w-full border-gray-300 rounded text-sm px-3 py-2 focus:border-cyan focus:ring-cyan">
                    @error('option_a')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Option B -->
                <div>
                    <label for="option_b" class="block text-xs font-bold text-gray-400 uppercase mb-2">Option B</label>
                    <input type="text" name="option_b" id="option_b" required placeholder="Option B choice"
                           class="w-full border-gray-300 rounded text-sm px-3 py-2 focus:border-cyan focus:ring-cyan">
                    @error('option_b')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Option C -->
                <div>
                    <label for="option_c" class="block text-xs font-bold text-gray-400 uppercase mb-2">Option C (Optional)</label>
                    <input type="text" name="option_c" id="option_c" placeholder="Option C choice"
                           class="w-full border-gray-300 rounded text-sm px-3 py-2 focus:border-cyan focus:ring-cyan">
                    @error('option_c')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Option D -->
                <div>
                    <label for="option_d" class="block text-xs font-bold text-gray-400 uppercase mb-2">Option D (Optional)</label>
                    <input type="text" name="option_d" id="option_d" placeholder="Option D choice"
                           class="w-full border-gray-300 rounded text-sm px-3 py-2 focus:border-cyan focus:ring-cyan">
                    @error('option_d')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Correct Option & Status -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Correct Option -->
                <div>
                    <label for="correct_option" class="block text-xs font-bold text-gray-400 uppercase mb-2">Correct Option(s)</label>
                    <input type="text" name="correct_option" id="correct_option" required placeholder="e.g. A or A,B (multi-select)"
                           class="w-full border-gray-300 rounded text-sm px-3 py-2 focus:border-cyan focus:ring-cyan">
                    <p class="text-[10px] text-gray-400 mt-1">Capital letters. Use comma separation for multiple correct answers.</p>
                    @error('correct_option')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Explanation -->
                <div>
                    <label for="explanation" class="block text-xs font-bold text-gray-400 uppercase mb-2">Answer Explanation / Reference</label>
                    <textarea name="explanation" id="explanation" rows="2" placeholder="Reference guide documentation explanation..."
                              class="w-full border-gray-300 rounded text-sm px-3 py-2 focus:border-cyan focus:ring-cyan"></textarea>
                    @error('explanation')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Is Active Checkbox -->
            <div class="flex items-center">
                <input type="checkbox" name="is_active" id="is_active" checked value="1"
                       class="rounded border-gray-300 text-cyan focus:ring-cyan h-4 w-4">
                <label for="is_active" class="ml-2 text-sm font-semibold text-gray-700">Mark as Active (Show in test engine sessions)</label>
            </div>

            <!-- Submit Buttons -->
            <div class="border-t border-gray-150 pt-6 flex justify-end space-x-3">
                <a href="{{ route('admin.questions.index') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-600 text-xs font-bold py-2.5 px-6 rounded transition">
                    Cancel
                </a>
                <button type="submit" class="bg-navy hover:bg-opacity-95 text-white text-xs font-bold py-2.5 px-6 rounded shadow transition">
                    Save Question
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
