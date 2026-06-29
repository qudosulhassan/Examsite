@extends('layouts.admin')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <div class="flex justify-between items-center">
        <h1 class="text-2xl font-bold text-gray-800">Edit Question #{{ $question->id }}</h1>
        <a href="{{ route('admin.questions.index', ['exam_id' => $question->exam_id]) }}" class="text-sm text-navy hover:underline">
            ← Back to Listing
        </a>
    </div>

    <!-- Edit Form -->
    <div class="bg-white border border-gray-250 rounded-lg p-6 shadow-sm">
        <form action="{{ route('admin.questions.update', $question->id) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Exam Select -->
                <div>
                    <label for="exam_id" class="block text-xs font-bold text-gray-400 uppercase mb-2">Certification Exam</label>
                    <select name="exam_id" id="exam_id" required
                            class="w-full border-gray-300 rounded text-sm px-3 py-2 focus:border-cyan focus:ring-cyan">
                        @foreach($exams as $exam)
                            <option value="{{ $exam->id }}" {{ old('exam_id', $question->exam_id) === $exam->id ? 'selected' : '' }}>
                                {{ $exam->exam_code }} - {{ $exam->exam_name }}
                            </option>
                        @endforeach
                    </select>
                    @error('exam_id')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Syllabus Topic -->
                <div>
                    <label for="topic" class="block text-xs font-bold text-gray-400 uppercase mb-2">Syllabus Topic / Chapter Name</label>
                    <input type="text" name="topic" id="topic" value="{{ old('topic', $question->topic) }}"
                           class="w-full border-gray-300 rounded text-sm px-3 py-2 focus:border-cyan focus:ring-cyan">
                    @error('topic')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Question Text -->
            <div>
                <label for="question_text" class="block text-xs font-bold text-gray-400 uppercase mb-2">Question Text</label>
                <textarea name="question_text" id="question_text" rows="4" required
                          class="w-full border-gray-300 rounded text-sm px-3 py-2 focus:border-cyan focus:ring-cyan">{{ old('question_text', $question->question_text) }}</textarea>
                @error('question_text')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Options Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Option A -->
                <div>
                    <label for="option_a" class="block text-xs font-bold text-gray-400 uppercase mb-2">Option A</label>
                    <input type="text" name="option_a" id="option_a" required value="{{ old('option_a', $question->option_a) }}"
                           class="w-full border-gray-300 rounded text-sm px-3 py-2 focus:border-cyan focus:ring-cyan">
                    @error('option_a')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Option B -->
                <div>
                    <label for="option_b" class="block text-xs font-bold text-gray-400 uppercase mb-2">Option B</label>
                    <input type="text" name="option_b" id="option_b" required value="{{ old('option_b', $question->option_b) }}"
                           class="w-full border-gray-300 rounded text-sm px-3 py-2 focus:border-cyan focus:ring-cyan">
                    @error('option_b')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Option C -->
                <div>
                    <label for="option_c" class="block text-xs font-bold text-gray-400 uppercase mb-2">Option C</label>
                    <input type="text" name="option_c" id="option_c" value="{{ old('option_c', $question->option_c) }}"
                           class="w-full border-gray-300 rounded text-sm px-3 py-2 focus:border-cyan focus:ring-cyan">
                    @error('option_c')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Option D -->
                <div>
                    <label for="option_d" class="block text-xs font-bold text-gray-400 uppercase mb-2">Option D</label>
                    <input type="text" name="option_d" id="option_d" value="{{ old('option_d', $question->option_d) }}"
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
                    <input type="text" name="correct_option" id="correct_option" required value="{{ old('correct_option', $question->correct_option) }}"
                           class="w-full border-gray-300 rounded text-sm px-3 py-2 focus:border-cyan focus:ring-cyan">
                    <p class="text-[10px] text-gray-400 mt-1">Capital letters. Use comma separation for multiple correct answers.</p>
                    @error('correct_option')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Explanation -->
                <div>
                    <label for="explanation" class="block text-xs font-bold text-gray-400 uppercase mb-2">Answer Explanation / Reference</label>
                    <textarea name="explanation" id="explanation" rows="2"
                              class="w-full border-gray-300 rounded text-sm px-3 py-2 focus:border-cyan focus:ring-cyan">{{ old('explanation', $question->explanation) }}</textarea>
                    @error('explanation')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Is Active Checkbox -->
            <div class="flex items-center">
                <input type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', $question->is_active) ? 'checked' : '' }}
                       class="rounded border-gray-300 text-cyan focus:ring-cyan h-4 w-4">
                <label for="is_active" class="ml-2 text-sm font-semibold text-gray-700">Mark as Active (Show in test engine sessions)</label>
            </div>

            <!-- Submit Buttons -->
            <div class="border-t border-gray-150 pt-6 flex justify-end space-x-3">
                <a href="{{ route('admin.questions.index', ['exam_id' => $question->exam_id]) }}" class="bg-gray-100 hover:bg-gray-200 text-gray-600 text-xs font-bold py-2.5 px-6 rounded transition">
                    Cancel
                </a>
                <button type="submit" class="bg-navy hover:bg-opacity-95 text-white text-xs font-bold py-2.5 px-6 rounded shadow transition">
                    Update Question
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
