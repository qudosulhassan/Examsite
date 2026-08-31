@extends('layouts.admin')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
        <h1 class="text-2xl font-bold text-gray-800">Manage Certification Exams</h1>
        <div class="flex items-center space-x-4">
            <div x-data="examSearch()" class="relative" @click.away="isOpen = false">
                <form action="{{ route('admin.exams.index') }}" method="GET" class="relative">
                    <input type="text" name="search" x-model="query" @input.debounce.300ms="fetchSuggestions" @focus="fetchSuggestions" placeholder="Search code or name..." class="w-64 border-gray-250 rounded-lg pl-3 pr-10 py-2 text-sm focus:ring-cyan focus:border-cyan shadow-sm" autocomplete="off">
                    <button type="submit" class="absolute right-0 top-0 mt-2 mr-3 text-gray-400 hover:text-cyan">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </button>
                </form>
                
                <!-- Suggestions Dropdown -->
                <div x-show="isOpen && suggestions.length > 0" x-transition x-cloak class="absolute z-50 w-full mt-1 bg-white rounded-lg shadow-lg border border-gray-200 overflow-hidden">
                    <ul class="max-h-60 overflow-y-auto">
                        <template x-for="exam in suggestions" :key="exam.id">
                            <li>
                                <a :href="`/admin/exams/${exam.id}/edit`" class="block px-4 py-2 hover:bg-gray-50 border-b border-gray-100 last:border-0">
                                    <div class="text-sm font-bold text-navy" x-text="exam.exam_code"></div>
                                    <div class="text-xs text-gray-500 truncate" x-text="exam.exam_name"></div>
                                </a>
                            </li>
                        </template>
                    </ul>
                </div>
            </div>
            <a href="{{ route('admin.exams.create') }}" class="bg-navy hover:bg-opacity-95 text-white text-xs font-bold py-2.5 px-4 rounded shadow transition whitespace-nowrap">
                + Create New Exam
            </a>
        </div>
    </div>

    <!-- Exams Table -->
    <div class="bg-white rounded-lg border border-gray-250 shadow-sm overflow-hidden">
        <table class="min-w-full divide-y divide-gray-150">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">Exam Code / Vendor</th>
                    <th class="px-6 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">Exam Name</th>
                    <th class="px-6 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">PDF Price</th>
                    <th class="px-6 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">Engine Price</th>
                    <th class="px-6 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-right text-[10px] font-bold text-gray-400 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-150 text-xs">
                @forelse($exams as $exam)
                    <tr>
                        <td class="px-6 py-4">
                            <span class="font-extrabold text-navy block">{{ $exam->exam_code }}</span>
                            <span class="text-[10px] text-gray-400 font-semibold">{{ $exam->vendor->name }}</span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="font-bold text-gray-700">{{ $exam->exam_name }}</div>
                            <div class="text-[10px] text-gray-400">{{ count($exam->topics ?: []) }} Topics | {{ $exam->difficulty }}</div>
                        </td>
                        <td class="px-6 py-4 font-semibold text-gray-700">
                            ${{ number_format($exam->price_pdf, 2) }}
                        </td>
                        <td class="px-6 py-4 font-semibold text-gray-700">
                            ${{ number_format($exam->price_engine, 2) }}
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-2.5 py-0.5 rounded-full text-[9px] font-bold uppercase {{ $exam->is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                {{ $exam->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right space-x-2 font-bold">
                            <a href="{{ route('admin.exams.edit', $exam->id) }}" class="text-cyan hover:underline">Edit</a>
                            <form action="{{ route('admin.exams.destroy', $exam->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Are you sure you want to delete this exam? This will delete all associated questions and attempts.')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-500 hover:underline">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-8 text-center text-gray-400">
                            No certification exams found in database.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        
        <!-- Pagination -->
        @if($exams->hasPages())
            <div class="bg-gray-50 px-6 py-4 border-t border-gray-150">
                {{ $exams->links() }}
            </div>
        @endif
    </div>
</div>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('examSearch', () => ({
        query: '{{ addslashes(request('search', '')) }}',
        suggestions: [],
        isOpen: false,
        fetchSuggestions() {
            if (this.query.length < 2) {
                this.suggestions = [];
                this.isOpen = false;
                return;
            }
            fetch(`/admin/exams/search-suggestions?query=${encodeURIComponent(this.query)}`)
                .then(res => res.json())
                .then(data => {
                    this.suggestions = data;
                    this.isOpen = data.length > 0;
                })
                .catch(err => console.error(err));
        }
    }));
});
</script>
@endsection
