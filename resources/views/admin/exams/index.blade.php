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

    <!-- SEO Diagnostic Filter Banner (if active) -->
    @if($seoIssue)
        <div class="bg-gradient-to-r from-navy/5 via-cyan/5 to-navy/5 border border-cyan/30 rounded-xl p-5 shadow-sm">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div class="space-y-1">
                    <div class="flex items-center gap-2">
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase tracking-wider bg-cyan/15 text-cyan border border-cyan/30">
                            SEO Diagnostic Filter
                        </span>
                        <span class="px-2 py-0.5 rounded-full text-xs font-black {{ $seoIssueCount === 0 ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700' }}">
                            {{ $seoIssueCount }} {{ $seoIssue === 'duplicate_title' ? 'Clusters' : 'Exams Flagged' }}
                        </span>
                    </div>
                    <h2 class="text-lg font-black text-navy">{{ $seoIssueTitle }}</h2>
                    <p class="text-xs text-gray-600 max-w-2xl leading-relaxed">
                        @if($seoIssue === 'missing_title')
                            Showing exams where the rendered <code class="font-mono text-navy font-bold">&lt;title&gt;</code> tag is missing, empty, or falling back to the site default. Click <strong>Edit</strong> on any exam to configure its custom Meta Title.
                        @elseif($seoIssue === 'missing_description')
                            Showing exams where the rendered <code class="font-mono text-navy font-bold">&lt;meta name="description"&gt;</code> is missing or empty. Click <strong>Edit</strong> to provide a dedicated Meta Description.
                        @elseif($seoIssue === 'duplicate_title')
                            Showing duplicate title collision groups where two or more published exams render the exact same public SEO title. Click <strong>Edit</strong> on individual exams to make each title unique.
                        @endif
                    </p>
                </div>
                <div class="flex items-center gap-2.5 flex-shrink-0">
                    <a href="{{ route('admin.exams.index') }}" class="bg-white hover:bg-gray-50 border border-gray-300 text-navy font-bold text-xs px-3.5 py-2 rounded-lg shadow-sm transition flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        <span>Clear Filter (All Exams)</span>
                    </a>
                    <a href="{{ route('admin.seo.index', ['tab' => 'health_score', 'refresh' => 1]) }}" class="bg-navy hover:bg-navy/90 text-white font-bold text-xs px-3.5 py-2 rounded-lg shadow-sm transition flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5 text-cyan" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                        <span>Re-run Audit</span>
                    </a>
                </div>
            </div>
        </div>
    @endif

    @if($seoIssue === 'missing_title')
        <!-- Missing Titles Table -->
        <div class="bg-white rounded-lg border border-gray-250 shadow-sm overflow-hidden">
            <table class="min-w-full divide-y divide-gray-150">
                <thead class="bg-gray-50 text-[10px] font-bold text-gray-400 uppercase tracking-wider">
                    <tr>
                        <th class="px-6 py-3 text-left">Exam</th>
                        <th class="px-6 py-3 text-left">Vendor</th>
                        <th class="px-6 py-3 text-left">Current SEO Title</th>
                        <th class="px-6 py-3 text-left">Public URL</th>
                        <th class="px-6 py-3 text-left">Issue</th>
                        <th class="px-6 py-3 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-150 text-xs">
                    @forelse($seoIssueData as $item)
                        <tr class="hover:bg-gray-50/60 transition">
                            <td class="px-6 py-4">
                                <span class="font-extrabold text-navy block text-sm">{{ $item['exam_code'] }}</span>
                                <span class="text-xs text-gray-500">{{ $item['exam_name'] }}</span>
                            </td>
                            <td class="px-6 py-4 font-semibold text-gray-700">
                                {{ $item['vendor_name'] }}
                            </td>
                            <td class="px-6 py-4">
                                <div class="font-mono text-[11px] text-gray-800 bg-gray-100 border border-gray-200 px-2.5 py-1.5 rounded-lg max-w-sm truncate" title="{{ $item['current_title'] }}">
                                    {{ $item['current_title'] ?: '(None / Empty)' }}
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <a href="{{ $item['url'] }}" target="_blank" class="text-cyan hover:underline font-mono text-[11px] inline-flex items-center gap-1">
                                    <span>{{ parse_url($item['url'], PHP_URL_PATH) }}</span>
                                    <svg class="w-3 h-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                </a>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider bg-rose-100 text-rose-800 border border-rose-200">
                                    {{ $item['issue'] }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <a href="{{ route('admin.exams.edit', $item['id']) }}" class="inline-flex items-center px-3 py-1.5 bg-navy hover:bg-navy/90 text-white rounded-lg text-xs font-bold shadow-sm transition">
                                    Edit
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center">
                                <div class="w-10 h-10 bg-emerald-100 text-emerald-600 rounded-full flex items-center justify-center mx-auto mb-2">
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                </div>
                                <h4 class="text-sm font-bold text-navy">All Clear — Zero Missing SEO Titles</h4>
                                <p class="text-xs text-gray-400 mt-0.5">All published exams have valid custom or generated SEO titles rendered.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

    @elseif($seoIssue === 'missing_description')
        <!-- Missing Descriptions Table -->
        <div class="bg-white rounded-lg border border-gray-250 shadow-sm overflow-hidden">
            <table class="min-w-full divide-y divide-gray-150">
                <thead class="bg-gray-50 text-[10px] font-bold text-gray-400 uppercase tracking-wider">
                    <tr>
                        <th class="px-6 py-3 text-left">Exam</th>
                        <th class="px-6 py-3 text-left">Vendor</th>
                        <th class="px-6 py-3 text-left">Current Meta Description</th>
                        <th class="px-6 py-3 text-left">Public URL</th>
                        <th class="px-6 py-3 text-left">Issue</th>
                        <th class="px-6 py-3 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-150 text-xs">
                    @forelse($seoIssueData as $item)
                        <tr class="hover:bg-gray-50/60 transition">
                            <td class="px-6 py-4">
                                <span class="font-extrabold text-navy block text-sm">{{ $item['exam_code'] }}</span>
                                <span class="text-xs text-gray-500">{{ $item['exam_name'] }}</span>
                            </td>
                            <td class="px-6 py-4 font-semibold text-gray-700">
                                {{ $item['vendor_name'] }}
                            </td>
                            <td class="px-6 py-4 max-w-sm">
                                <div class="text-[11px] text-gray-700 bg-gray-50 border border-gray-200 p-2 rounded-lg line-clamp-2" title="{{ $item['current_description'] }}">
                                    {{ $item['current_description'] ?: '(None / Empty)' }}
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <a href="{{ $item['url'] }}" target="_blank" class="text-cyan hover:underline font-mono text-[11px] inline-flex items-center gap-1">
                                    <span>{{ parse_url($item['url'], PHP_URL_PATH) }}</span>
                                    <svg class="w-3 h-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                </a>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider bg-rose-100 text-rose-800 border border-rose-200">
                                    {{ $item['issue'] }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <a href="{{ route('admin.exams.edit', $item['id']) }}" class="inline-flex items-center px-3 py-1.5 bg-navy hover:bg-navy/90 text-white rounded-lg text-xs font-bold shadow-sm transition">
                                    Edit
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center">
                                <div class="w-10 h-10 bg-emerald-100 text-emerald-600 rounded-full flex items-center justify-center mx-auto mb-2">
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                </div>
                                <h4 class="text-sm font-bold text-navy">All Clear — Zero Missing Meta Descriptions</h4>
                                <p class="text-xs text-gray-400 mt-0.5">All published exams have valid custom or generated meta descriptions rendered.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

    @elseif($seoIssue === 'duplicate_title')
        <!-- Duplicate Title Clusters View -->
        <div class="space-y-5">
            @forelse($seoIssueData as $cluster)
                <div class="bg-white rounded-xl border border-gray-250 shadow-sm overflow-hidden">
                    <div class="bg-gray-50/80 px-6 py-3.5 border-b border-gray-200 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                        <div class="flex items-center space-x-3 min-w-0">
                            <span class="w-3 h-3 rounded-full bg-amber-500 flex-shrink-0 animate-pulse"></span>
                            <div class="min-w-0">
                                <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider block">Duplicate Rendered SEO Title</span>
                                <h4 class="text-sm font-extrabold text-navy truncate" title="{{ $cluster['title'] }}">
                                    "{{ $cluster['title'] }}"
                                </h4>
                            </div>
                        </div>
                        <span class="px-3 py-1 rounded-full text-xs font-black bg-amber-100 text-amber-800 border border-amber-200 flex-shrink-0 self-start sm:self-auto">
                            {{ $cluster['count'] }} Exams Colliding
                        </span>
                    </div>
                    <table class="min-w-full divide-y divide-gray-150">
                        <thead class="bg-gray-50/50 text-[10px] font-bold text-gray-400 uppercase tracking-wider">
                            <tr>
                                <th class="px-6 py-2.5 text-left">Exam</th>
                                <th class="px-6 py-2.5 text-left">Vendor</th>
                                <th class="px-6 py-2.5 text-left">Public URL</th>
                                <th class="px-6 py-2.5 text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 text-xs">
                            @foreach($cluster['exams'] as $ex)
                                <tr class="hover:bg-gray-50/60 transition">
                                    <td class="px-6 py-3.5">
                                        <span class="font-extrabold text-navy block">{{ $ex['exam_code'] }}</span>
                                        <span class="text-xs text-gray-500">{{ $ex['exam_name'] }}</span>
                                    </td>
                                    <td class="px-6 py-3.5 font-semibold text-gray-700">
                                        {{ $ex['vendor_name'] }}
                                    </td>
                                    <td class="px-6 py-3.5">
                                        <a href="{{ $ex['url'] }}" target="_blank" class="text-cyan hover:underline font-mono text-[11px] inline-flex items-center gap-1">
                                            <span>{{ parse_url($ex['url'], PHP_URL_PATH) }}</span>
                                            <svg class="w-3 h-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                        </a>
                                    </td>
                                    <td class="px-6 py-3.5 text-right">
                                        <a href="{{ route('admin.exams.edit', $ex['id']) }}" class="inline-flex items-center px-3 py-1.5 bg-navy hover:bg-navy/90 text-white rounded-lg text-xs font-bold shadow-sm transition">
                                            Edit
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @empty
                <div class="bg-white rounded-xl border border-gray-250 p-12 text-center shadow-sm">
                    <div class="w-12 h-12 bg-emerald-100 text-emerald-600 rounded-full flex items-center justify-center mx-auto mb-2">
                        <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                    </div>
                    <h4 class="text-sm font-bold text-navy">All Clear — No Duplicate Title Clusters</h4>
                    <p class="text-xs text-gray-400 mt-0.5">Every published certification exam renders a unique SEO title.</p>
                </div>
            @endforelse
        </div>

    @else
        <!-- Standard Exams Table -->
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
                                <span class="text-[10px] text-gray-400 font-semibold">{{ $exam->vendor ? $exam->vendor->name : 'No Vendor' }}</span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="font-bold text-gray-700 flex items-center gap-2">
                                    <span>{{ $exam->exam_name }}</span>
                                    <span class="bg-navy/5 text-navy font-semibold px-2 py-0.5 rounded text-[10px] border border-navy/10">{{ $exam->question_count }} Q's</span>
                                </div>
                                @if($exam->header_title)
                                    <div class="text-[11px] text-cyan font-bold truncate max-w-md mt-0.5">
                                        <span class="text-gray-400 font-normal">H1:</span> {{ $exam->header_title }}
                                    </div>
                                @endif
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
    @endif
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
