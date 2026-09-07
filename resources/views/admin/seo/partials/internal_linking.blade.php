<div class="space-y-6">
    <div class="bg-white border border-gray-200 rounded-xl p-6 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="space-y-1">
            <div class="flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-cyan animate-pulse"></span>
                <h4 class="text-base font-extrabold text-navy">Internal Linking &amp; Crawl Management</h4>
                <span class="px-2 py-0.5 text-[10px] font-bold bg-cyan/10 text-navy border border-cyan/30 rounded-full">Site Architecture &amp; PageRank</span>
            </div>
            <p class="text-xs text-gray-500">
                Ensure every important exam and article is reachable within 2-3 clicks from the homepage to maximize Google crawl budget and ranking power.
            </p>
        </div>
    </div>

    <!-- Metrics Grid -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div class="bg-white border border-gray-200 rounded-xl p-4 shadow-sm text-center">
            <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Crawlable Pages</span>
            <div class="text-2xl font-black text-navy mt-1">{{ $internalLinking['total_crawlable'] }}</div>
            <span class="text-[10px] text-emerald-600 font-semibold">Indexed in Sitemap</span>
        </div>

        <div class="bg-white border border-gray-200 rounded-xl p-4 shadow-sm text-center">
            <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Blocked from Crawlers</span>
            <div class="text-2xl font-black text-gray-600 mt-1">{{ $internalLinking['disallowed_urls'] }}</div>
            <span class="text-[10px] text-gray-400 font-semibold">Admin, Cart, User paths</span>
        </div>

        <div class="bg-white border border-gray-200 rounded-xl p-4 shadow-sm text-center">
            <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Orphan Pages</span>
            <div class="text-2xl font-black {{ $internalLinking['orphan_count'] === 0 ? 'text-emerald-600' : 'text-amber-600' }} mt-1">
                {{ $internalLinking['orphan_count'] }}
            </div>
            <span class="text-[10px] text-gray-400 font-semibold">Pages with 0 internal links</span>
        </div>

        <div class="bg-white border border-gray-200 rounded-xl p-4 shadow-sm text-center">
            <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Low-Link Warning</span>
            <div class="text-2xl font-black {{ $internalLinking['low_link_count'] === 0 ? 'text-emerald-600' : 'text-amber-600' }} mt-1">
                {{ $internalLinking['low_link_count'] }}
            </div>
            <span class="text-[10px] text-gray-400 font-semibold">&lt; 3 internal references</span>
        </div>
    </div>

    <!-- Crawl Depth Distribution -->
    <div class="bg-white border border-gray-200 rounded-xl p-6 shadow-sm space-y-6">
        <h5 class="text-xs font-bold text-navy uppercase tracking-wider border-b border-gray-150 pb-3">Crawl Depth Hierarchy</h5>

        <div class="space-y-4">
            @foreach($internalLinking['crawl_depth_distribution'] as $level => $count)
                <div class="space-y-1">
                    <div class="flex items-center justify-between text-xs">
                        <span class="font-bold text-navy">{{ $level }}</span>
                        <span class="font-mono text-gray-600 font-semibold">{{ $count }} URLs</span>
                    </div>
                    <div class="w-full bg-gray-100 h-2.5 rounded-full overflow-hidden">
                        <div class="bg-cyan h-full rounded-full" style="width: {{ min(100, max(10, ($count / max(1, $internalLinking['total_crawlable'])) * 100)) }}%"></div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Internal Linking Best Practices Guide -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div class="bg-white border border-gray-200 rounded-xl p-5 shadow-sm space-y-2">
            <div class="flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                <h6 class="text-xs font-bold text-navy">Exam to Exam Related Links</h6>
            </div>
            <p class="text-xs text-gray-600 leading-relaxed">
                Exam detail pages automatically display "Related Certification Exams" from the same vendor to build strong contextual internal link clusters.
            </p>
        </div>

        <div class="bg-white border border-gray-200 rounded-xl p-5 shadow-sm space-y-2">
            <div class="flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-cyan"></span>
                <h6 class="text-xs font-bold text-navy">Vendor Hub Siloing</h6>
            </div>
            <p class="text-xs text-gray-600 leading-relaxed">
                Every vendor (Cisco, Microsoft, AWS, CompTIA) has a dedicated hub page listing all associated certifications and exams, preventing orphaned URLs.
            </p>
        </div>
    </div>
</div>
