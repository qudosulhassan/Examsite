<div class="space-y-6">
    <!-- Top Health Score Card -->
    <div class="bg-gradient-to-r from-navy via-[#0c1e38] to-navy rounded-2xl p-6 sm:p-8 text-white shadow-xl relative overflow-hidden border border-gray-800">
        <div class="absolute -right-10 -bottom-10 w-72 h-72 bg-cyan/10 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute right-40 -top-10 w-56 h-56 bg-emerald-500/10 rounded-full blur-2xl pointer-events-none"></div>

        <div class="relative z-10 flex flex-col md:flex-row items-center justify-between gap-6">
            <div class="flex items-center space-x-6">
                <!-- Circular Score Gauge -->
                <div class="relative flex items-center justify-center w-28 h-28 flex-shrink-0">
                    <svg class="w-full h-full transform -rotate-90" viewBox="0 0 36 36">
                        <path class="text-gray-800" stroke-width="3.5" stroke="currentColor" fill="none" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                        <path class="{{ $healthAudit['score'] >= 80 ? 'text-cyan' : ($healthAudit['score'] >= 60 ? 'text-amber-400' : 'text-rose-500') }}" 
                              stroke-dasharray="{{ $healthAudit['score'] }}, 100" 
                              stroke-width="3.5" 
                              stroke-linecap="round" 
                              stroke="currentColor" 
                              fill="none" 
                              d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                    </svg>
                    <div class="absolute flex flex-col items-center justify-center">
                        <span class="text-2xl font-black tracking-tight text-white">{{ $healthAudit['score'] }}%</span>
                        <span class="text-[9px] uppercase font-bold text-gray-400 tracking-wider">Health</span>
                    </div>
                </div>

                <div class="space-y-1">
                    <div class="inline-flex items-center gap-2 px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $healthAudit['score'] >= 80 ? 'bg-emerald-500/20 text-emerald-300 border border-emerald-500/30' : 'bg-amber-500/20 text-amber-300 border border-amber-500/30' }}">
                        <span class="w-2 h-2 rounded-full {{ $healthAudit['score'] >= 80 ? 'bg-emerald-400' : 'bg-amber-400' }} animate-pulse"></span>
                        {{ $healthAudit['score'] >= 80 ? 'Healthy SEO Architecture' : 'Action Recommended' }}
                    </div>
                    <h3 class="text-xl font-extrabold tracking-tight text-white">Technical SEO Health Score</h3>
                    <p class="text-xs text-gray-300 max-w-xl leading-relaxed">
                        Comprehensive real-time analysis across {{ $healthAudit['total_checks'] }} vital search engine parameters including indexation, canonical integrity, sitemap freshness, redirects, and schema validity.
                    </p>
                </div>
            </div>

            <!-- Metric summary badges -->
            <div class="flex items-center gap-3 self-stretch md:self-center justify-around md:justify-end border-t md:border-t-0 md:border-l border-gray-700/60 pt-4 md:pt-0 md:pl-8">
                <div class="text-center px-3">
                    <div class="text-2xl font-black text-emerald-400">{{ $healthAudit['passed_count'] }}</div>
                    <div class="text-[10px] uppercase font-bold tracking-wider text-gray-400">Passed</div>
                </div>
                <div class="h-8 w-px bg-gray-800"></div>
                <div class="text-center px-3">
                    <div class="text-2xl font-black text-amber-400">{{ $healthAudit['warning_count'] }}</div>
                    <div class="text-[10px] uppercase font-bold tracking-wider text-gray-400">Warnings</div>
                </div>
                <div class="h-8 w-px bg-gray-800"></div>
                <div class="text-center px-3">
                    <div class="text-2xl font-black text-rose-400">{{ $healthAudit['critical_count'] }}</div>
                    <div class="text-[10px] uppercase font-bold tracking-wider text-gray-400">Critical</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Health Checks List -->
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
        <div class="border-b border-gray-150 px-6 py-4 bg-gray-50/70 flex items-center justify-between">
            <div>
                <h4 class="text-sm font-bold text-navy uppercase tracking-wide">Automated Technical SEO Audits</h4>
                <p class="text-xs text-gray-500">Live checks computed from your active database, routing configuration, and template outputs.</p>
            </div>
            <a href="{{ route('admin.seo.index', ['tab' => 'health_score', 'refresh' => 1]) }}" class="inline-flex items-center px-3 py-1.5 text-xs font-semibold text-navy bg-white border border-gray-300 rounded-lg hover:bg-gray-50 shadow-sm transition">
                <svg class="w-3.5 h-3.5 mr-1 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                Re-run Audit
            </a>
        </div>

        <div class="divide-y divide-gray-100">
            @foreach($healthAudit['checks'] as $key => $check)
                <div class="p-5 flex items-center justify-between hover:bg-gray-50/60 transition gap-4">
                    <div class="flex items-start space-x-3.5 min-w-0">
                        @if($check['status'] === 'passed')
                            <div class="w-7 h-7 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center flex-shrink-0 mt-0.5">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                            </div>
                        @elseif($check['status'] === 'warning')
                            <div class="w-7 h-7 rounded-full bg-amber-100 text-amber-600 flex items-center justify-center flex-shrink-0 mt-0.5">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                            </div>
                        @else
                            <div class="w-7 h-7 rounded-full bg-rose-100 text-rose-600 flex items-center justify-center flex-shrink-0 mt-0.5">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
                            </div>
                        @endif

                        <div class="min-w-0">
                            <div class="flex items-center gap-2">
                                <h5 class="text-sm font-bold text-navy">{{ $check['title'] }}</h5>
                                @if($check['status'] === 'passed')
                                    <span class="px-2 py-0.5 text-[10px] font-extrabold uppercase rounded-full bg-emerald-100 text-emerald-700">Passed</span>
                                @elseif($check['status'] === 'warning')
                                    <span class="px-2 py-0.5 text-[10px] font-extrabold uppercase rounded-full bg-amber-100 text-amber-800">Warning</span>
                                @else
                                    <span class="px-2 py-0.5 text-[10px] font-extrabold uppercase rounded-full bg-rose-100 text-rose-800">Critical</span>
                                @endif
                            </div>
                            <p class="text-xs text-gray-500 mt-0.5">{{ $check['desc'] }}</p>
                        </div>
                    </div>

                    <div class="flex items-center space-x-3 flex-shrink-0">
                        @if(isset($check['action_url']))
                            <a href="{{ $check['action_url'] }}" class="text-xs font-bold text-navy hover:text-cyan transition flex items-center gap-1 bg-gray-100 hover:bg-gray-200 px-3 py-1.5 rounded-lg">
                                <span>{{ $check['action_label'] ?? 'Inspect' }}</span>
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                            </a>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
