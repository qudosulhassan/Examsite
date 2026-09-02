@extends('layouts.admin')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Question Import History</h1>
            <p class="text-xs text-gray-500 mt-1">Audit trail and execution logs for bulk question bank uploads.</p>
        </div>
        <div class="flex items-center space-x-3">
            <a href="{{ route('admin.questions.import-form') }}" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-xs font-bold rounded text-white bg-navy hover:bg-opacity-95 transition">
                <svg class="h-4 w-4 mr-1.5 text-cyan" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
                Import New Questions
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
            <a href="{{ route('admin.questions.import-pdf-form') }}" class="border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-3 px-1 border-b-2 font-bold text-xs">
                Import PDF
            </a>
            <a href="{{ route('admin.questions.import-history') }}" class="border-orange text-orange whitespace-nowrap py-3 px-1 border-b-2 font-bold text-xs">
                Import History
            </a>
        </nav>
    </div>

    @if(session('success'))
        <div class="bg-green-50 border-l-4 border-green-500 p-4 rounded-r shadow-sm">
            <p class="text-xs text-green-700 font-bold">{{ session('success') }}</p>
        </div>
    @endif

    <!-- Batches Table -->
    <div class="bg-white border border-gray-250 rounded-lg shadow-sm overflow-hidden">
        @if($batches->isEmpty())
            <div class="p-12 text-center text-gray-400">
                <svg class="mx-auto h-12 w-12 text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <p class="text-sm font-semibold text-gray-600">No import history found</p>
                <p class="text-xs text-gray-400 mt-1">Upload a JSON question file to create your first import batch.</p>
                <a href="{{ route('admin.questions.import-form') }}" class="mt-4 inline-block px-4 py-2 bg-navy text-white text-xs font-bold rounded hover:bg-opacity-90">
                    Import Questions Now
                </a>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-left text-xs">
                    <thead class="bg-gray-50 text-gray-500 font-bold uppercase tracking-wider">
                        <tr>
                            <th class="p-3">Batch ID / Date</th>
                            <th class="p-3">Filename</th>
                            <th class="p-3">Format</th>
                            <th class="p-3 text-center">Total</th>
                            <th class="p-3 text-center text-green-600">Valid</th>
                            <th class="p-3 text-center text-yellow-600">Warn</th>
                            <th class="p-3 text-center text-red-600">Errors</th>
                            <th class="p-3 text-center text-orange">Dups</th>
                            <th class="p-3 text-center text-navy">Imported</th>
                            <th class="p-3">Status</th>
                            <th class="p-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-150 bg-white">
                        @foreach($batches as $batch)
                            <tr class="hover:bg-gray-50 transition">
                                <td class="p-3">
                                    <div class="font-bold text-navy">{{ $batch->uuid }}</div>
                                    <div class="text-[11px] text-gray-400">{{ $batch->created_at->format('M d, Y H:i') }}</div>
                                </td>
                                <td class="p-3 font-semibold text-gray-800">
                                    {{ $batch->filename }}
                                </td>
                                <td class="p-3">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-mono bg-gray-100 text-gray-700">
                                        {{ $batch->format_detected ?? 'Universal V2' }}
                                    </span>
                                </td>
                                <td class="p-3 text-center font-bold text-gray-700">{{ $batch->total_questions }}</td>
                                <td class="p-3 text-center font-bold text-green-600">{{ $batch->valid_count }}</td>
                                <td class="p-3 text-center font-bold text-yellow-600">{{ $batch->warning_count }}</td>
                                <td class="p-3 text-center font-bold text-red-600">{{ $batch->error_count }}</td>
                                <td class="p-3 text-center font-bold text-orange">{{ $batch->duplicate_count }}</td>
                                <td class="p-3 text-center font-bold text-navy">{{ $batch->imported_count }}</td>
                                <td class="p-3">
                                    @if($batch->status === 'completed')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold bg-green-100 text-green-800">
                                            Completed
                                        </span>
                                    @elseif($batch->status === 'completed_with_errors')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold bg-yellow-100 text-yellow-800">
                                            With Errors
                                        </span>
                                    @elseif($batch->status === 'ready_for_review')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold bg-blue-100 text-blue-800">
                                            Ready for Review
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold bg-gray-100 text-gray-700">
                                            {{ ucfirst($batch->status) }}
                                        </span>
                                    @endif
                                </td>
                                <td class="p-3 text-right space-x-2 whitespace-nowrap">
                                    <a href="{{ route('admin.questions.import-review', $batch->uuid) }}" class="text-cyan font-bold hover:underline">
                                        Review
                                    </a>
                                    <a href="{{ route('admin.questions.import-error-report', $batch->uuid) }}" class="text-gray-600 font-bold hover:underline">
                                        Report
                                    </a>
                                    <form action="{{ route('admin.questions.import-cancel-batch', $batch->uuid) }}" method="POST" class="inline" onsubmit="return confirm('Delete this import record?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-500 font-bold hover:underline">
                                            Delete
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="p-4 border-t border-gray-200">
                {{ $batches->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
