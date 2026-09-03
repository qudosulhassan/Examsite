<!-- Search Status Notice -->
@if(!empty($searchQuery) || !empty($vendorFilter))
    <div class="max-w-4xl mx-auto flex flex-col sm:flex-row items-center justify-between bg-cyan/10 border border-cyan/20 p-4 rounded-2xl mb-8 gap-3">
        <div class="flex items-center space-x-2 text-sm text-navy font-bold">
            <svg class="w-5 h-5 text-cyan shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span>Found {{ $compatibleExams->total() }} practice test(s) matching your filter.</span>
        </div>
        <button type="button" @click="resetFilters()" class="text-xs font-black text-cyan hover:underline uppercase shrink-0">Clear Filters &times;</button>
    </div>
@endif

<!-- Exam Cards Grid -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
    @forelse($compatibleExams as $exam)
        <div class="bg-white p-8 rounded-3xl border border-gray-100 shadow-[0_10px_40px_rgba(0,0,0,0.04)] flex flex-col justify-between hover:-translate-y-1 transition-all duration-300 group">
            <div>
                <div class="flex justify-between items-center mb-6">
                    <span class="bg-gray-50 border border-gray-200 text-gray-600 text-[10px] font-black uppercase tracking-wider px-3 py-1.5 rounded-md">
                        {{ $exam->vendor->name }}
                    </span>
                    <span class="text-xs font-bold text-cyan bg-cyan/10 px-3 py-1.5 rounded-md">
                        {{ $exam->questions_count ?? $exam->questions()->count() }} Qs
                    </span>
                </div>
                <h3 class="text-xl font-black text-navy mb-2 group-hover:text-cyan transition-colors">
                    <a href="{{ route('public.demo-test-engine.lobby', $exam->slug) }}">
                        {{ $exam->exam_code }}
                    </a>
                </h3>
                <p class="text-[13px] font-medium text-gray-500 mb-8 truncate">
                    <a href="{{ route('public.demo-test-engine.lobby', $exam->slug) }}" class="hover:underline">
                        {{ $exam->exam_name }}
                    </a>
                </p>
            </div>
            
            <div class="flex items-center justify-between pt-6 border-t border-gray-100">
                <span class="text-[11px] font-bold text-gray-400 uppercase tracking-widest">Passing: <strong class="text-navy text-sm">{{ $exam->passing_score }}%</strong></span>
                <a href="{{ route('public.demo-test-engine.lobby', $exam->slug) }}" class="bg-gray-100 hover:bg-navy text-navy hover:text-white text-[11px] font-black uppercase tracking-wider px-4 py-2.5 rounded-xl transition-all duration-300 flex items-center space-x-1">
                    <span>Launch Demo</span>
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                </a>
            </div>
        </div>
    @empty
        <div class="col-span-1 md:col-span-2 lg:col-span-3 bg-white p-12 rounded-3xl text-center border border-gray-200/80 shadow-sm space-y-4">
            <div class="w-16 h-16 bg-gray-100 text-gray-400 rounded-full flex items-center justify-center mx-auto">
                <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </div>
            <h3 class="text-lg font-bold text-navy">No practice tests matched your search</h3>
            <p class="text-sm text-gray-500 max-w-md mx-auto">Try searching for a different exam code (e.g. 200-301, AZ-900) or clear your filter settings.</p>
            <div class="pt-2">
                <button type="button" @click="resetFilters()" class="inline-flex items-center space-x-2 bg-navy text-white px-6 py-2.5 rounded-xl text-xs font-bold uppercase tracking-wider shadow">
                    <span>Reset Search</span>
                </button>
            </div>
        </div>
    @endforelse
</div>

<!-- Pagination Links -->
@if($compatibleExams->hasPages())
    <div class="mt-12 ajax-pagination">
        {{ $compatibleExams->links() }}
    </div>
@endif
