<div class="space-y-6">
    <div class="bg-white border border-gray-200 rounded-xl p-6 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="space-y-1">
            <div class="flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-cyan animate-pulse"></span>
                <h4 class="text-base font-extrabold text-navy">Performance &amp; Core Web Vitals</h4>
                <span class="px-2 py-0.5 text-[10px] font-bold bg-cyan/10 text-navy border border-cyan/30 rounded-full">Google Ranking Factor</span>
            </div>
            <p class="text-xs text-gray-500">
                Page speed and interactive responsiveness directly impact Google mobile search rankings and conversion rates.
            </p>
        </div>

        <a href="https://pagespeed.web.dev/analysis?url={{ urlencode(url('/')) }}" target="_blank" class="inline-flex items-center px-4 py-2 text-xs font-bold text-navy bg-white border border-gray-300 hover:bg-gray-50 rounded-lg shadow-sm transition gap-1.5">
            <span>Test on PageSpeed Insights</span>
            <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
        </a>
    </div>

    <!-- Core Web Vitals Benchmark Targets -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-white border border-gray-200 rounded-xl p-5 shadow-sm space-y-2">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-gray-400">LCP</span>
                <span class="px-2 py-0.5 text-[10px] font-bold bg-emerald-100 text-emerald-800 rounded-full">Good Target: &le; 2.5s</span>
            </div>
            <h5 class="text-base font-extrabold text-navy">Largest Contentful Paint</h5>
            <p class="text-xs text-gray-500 leading-relaxed">
                Measures perceived loading speed by recording when the main content block finishes rendering.
            </p>
        </div>

        <div class="bg-white border border-gray-200 rounded-xl p-5 shadow-sm space-y-2">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-gray-400">INP</span>
                <span class="px-2 py-0.5 text-[10px] font-bold bg-emerald-100 text-emerald-800 rounded-full">Good Target: &le; 200ms</span>
            </div>
            <h5 class="text-base font-extrabold text-navy">Interaction to Next Paint</h5>
            <p class="text-xs text-gray-500 leading-relaxed">
                Measures UI responsiveness and click latency throughout the user lifecycle on your pages.
            </p>
        </div>

        <div class="bg-white border border-gray-200 rounded-xl p-5 shadow-sm space-y-2">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-gray-400">CLS</span>
                <span class="px-2 py-0.5 text-[10px] font-bold bg-emerald-100 text-emerald-800 rounded-full">Good Target: &le; 0.1</span>
            </div>
            <h5 class="text-base font-extrabold text-navy">Cumulative Layout Shift</h5>
            <p class="text-xs text-gray-500 leading-relaxed">
                Measures visual stability and prevents unexpected shifts while practice test pages load.
            </p>
        </div>
    </div>

    <!-- Performance Audit Checklist -->
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-150 bg-gray-50/70">
            <h5 class="text-xs font-bold text-navy uppercase tracking-wider">Infrastructure &amp; Optimization Checklist</h5>
        </div>

        <div class="divide-y divide-gray-100 text-xs">
            <div class="p-4 flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <span class="w-6 h-6 rounded-full {{ $performance['opcache_enabled'] ? 'bg-emerald-100 text-emerald-600' : 'bg-amber-100 text-amber-600' }} flex items-center justify-center font-bold">
                        {{ $performance['opcache_enabled'] ? '✓' : '!' }}
                    </span>
                    <div>
                        <div class="font-bold text-navy">PHP OPcache Bytecode Caching</div>
                        <div class="text-gray-500">Compiles PHP scripts to shared memory for near-zero execution overhead.</div>
                    </div>
                </div>
                <span class="font-semibold {{ $performance['opcache_enabled'] ? 'text-emerald-700' : 'text-amber-700' }}">
                    {{ $performance['opcache_enabled'] ? 'Enabled' : 'Disabled (Local Dev)' }}
                </span>
            </div>

            <div class="p-4 flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <span class="w-6 h-6 rounded-full {{ $performance['gzip_supported'] ? 'bg-emerald-100 text-emerald-600' : 'bg-rose-100 text-rose-600' }} flex items-center justify-center font-bold">
                        {{ $performance['gzip_supported'] ? '✓' : '✗' }}
                    </span>
                    <div>
                        <div class="font-bold text-navy">Gzip / Zlib HTTP Compression</div>
                        <div class="text-gray-500">Compresses HTML responses and API payloads before transmitting to users.</div>
                    </div>
                </div>
                <span class="font-semibold text-emerald-700">Supported</span>
            </div>

            <div class="p-4 flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <span class="w-6 h-6 rounded-full {{ $performance['assets_versioned'] ? 'bg-emerald-100 text-emerald-600' : 'bg-blue-100 text-blue-600' }} flex items-center justify-center font-bold">
                        {{ $performance['assets_versioned'] ? '✓' : 'i' }}
                    </span>
                    <div>
                        <div class="font-bold text-navy">Vite Production Asset Versioning</div>
                        <div class="text-gray-500">Builds minified CSS/JS with cache-busting hashes for instant CDN delivery.</div>
                    </div>
                </div>
                <span class="font-semibold {{ $performance['assets_versioned'] ? 'text-emerald-700' : 'text-blue-700' }}">
                    {{ $performance['assets_versioned'] ? 'Manifest Built' : 'Vite Dev Mode' }}
                </span>
            </div>

            <div class="p-4 flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <span class="w-6 h-6 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center font-bold">
                        ✓
                    </span>
                    <div>
                        <div class="font-bold text-navy">Modern Web Fonts Optimization</div>
                        <div class="text-gray-500">Google Fonts loaded with preconnect and font-display: swap.</div>
                    </div>
                </div>
                <span class="font-semibold text-emerald-700">Active</span>
            </div>
        </div>
    </div>
</div>
