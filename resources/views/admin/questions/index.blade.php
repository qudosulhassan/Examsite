@extends('layouts.admin')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
        <h1 class="text-2xl font-bold text-gray-800">Manage Certification Questions</h1>
        <div class="flex space-x-2">
            <a href="{{ route('admin.questions.create') }}" class="bg-navy hover:bg-opacity-95 text-white text-xs font-bold py-2 px-4 rounded shadow transition">
                + Add New Question
            </a>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="bg-white p-4 border border-gray-250 rounded-lg shadow-sm">
        <form action="{{ route('admin.questions.index') }}" method="GET" class="flex flex-col sm:flex-row items-end gap-4">
            <div class="flex-grow">
                <label for="exam_id" class="block text-xs font-bold text-gray-400 uppercase mb-2">Filter by Certification Exam</label>
                <select name="exam_id" id="exam_id" 
                        class="w-full border-gray-300 rounded text-sm px-3 py-2 focus:border-cyan focus:ring-cyan">
                    <option value="">-- All Certification Exams --</option>
                    @foreach($exams as $exam)
                        <option value="{{ $exam->id }}" {{ $examId == $exam->id ? 'selected' : '' }}>
                            {{ $exam->exam_code }} - {{ $exam->exam_name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="flex space-x-2">
                <button type="submit" class="bg-navy text-white text-xs font-bold px-6 py-2.5 rounded hover:bg-opacity-95 transition">
                    Apply Filter
                </button>
                @if($examId)
                    <a href="{{ route('admin.questions.index') }}" class="bg-gray-100 text-gray-600 text-xs font-bold px-4 py-2.5 rounded hover:bg-gray-200 transition">
                        Clear
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Questions Table -->
    <div class="bg-white rounded-lg border border-gray-250 shadow-sm overflow-hidden">
        <table class="min-w-full divide-y divide-gray-150">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">Exam</th>
                    <th class="px-6 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">Question Text</th>
                    <th class="px-6 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">Topic</th>
                    <th class="px-6 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">Answer</th>
                    <th class="px-6 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-right text-[10px] font-bold text-gray-400 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-150 text-xs">
                @forelse($questions as $question)
                    <tr>
                        <td class="px-6 py-4 font-bold text-navy">
                            {{ $question->exam->exam_code }}
                        </td>
                        <td class="px-6 py-4 text-gray-700 max-w-sm">
                            <div class="font-medium line-clamp-2">{{ $question->question_text }}</div>
                        </td>
                        <td class="px-6 py-4 text-gray-500 font-semibold">
                            {{ $question->topic ?: 'General' }}
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-0.5 rounded bg-cyan bg-opacity-10 text-navy font-bold">
                                {{ $question->correct_option }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-0.5 rounded-full text-[9px] font-bold uppercase {{ $question->is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                {{ $question->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right space-x-2 font-bold">
                            <a href="{{ route('admin.questions.edit', $question->id) }}" class="text-cyan hover:underline">Edit</a>
                            <form action="{{ route('admin.questions.destroy', $question->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Are you sure you want to delete this question?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-500 hover:underline">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-8 text-center text-gray-400">
                            No certification questions found matching criteria.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        
        <!-- Pagination -->
        @if($questions->hasPages())
            <div class="bg-gray-50 px-6 py-4 border-t border-gray-150">
                {{ $questions->appends(request()->query())->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
