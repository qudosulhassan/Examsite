<div class="space-y-6">
    <!-- Robots Status & Quick Actions -->
    <div class="bg-white border border-gray-200 rounded-xl p-6 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="space-y-1">
            <div class="flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-cyan animate-pulse"></span>
                <h4 class="text-base font-extrabold text-navy">Robots.txt Directives Manager</h4>
                <span class="px-2 py-0.5 text-[10px] font-bold bg-cyan/10 text-navy border border-cyan/30 rounded-full">Active File &amp; Dynamic Fallback</span>
            </div>
            <p class="text-xs text-gray-500">
                Controls crawl accessibility for Googlebot, Bingbot, and other compliant web crawlers. Changes are immediately synchronized to disk.
            </p>
            <div class="flex items-center gap-2 mt-2 pt-2 border-t border-gray-100">
                <span class="text-xs font-mono bg-gray-50 border border-gray-200 px-3 py-1 rounded-lg text-gray-700 font-semibold select-all">
                    {{ url('/robots.txt') }}
                </span>
                <a href="{{ url('/robots.txt') }}" target="_blank" class="inline-flex items-center px-2.5 py-1 text-xs font-bold text-navy hover:text-cyan bg-gray-100 hover:bg-gray-200 rounded-lg transition gap-1">
                    <span>View Live robots.txt</span>
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                </a>
            </div>
        </div>

        <div class="flex items-center gap-2">
            <form action="{{ route('admin.seo.robots.reset') }}" method="POST" onsubmit="return confirm('Reset robots.txt back to recommended security standards?');">
                @csrf
                <button type="submit" class="inline-flex items-center px-3.5 py-2 text-xs font-bold text-gray-700 bg-white border border-gray-300 hover:bg-gray-50 rounded-lg shadow-sm transition">
                    <svg class="w-3.5 h-3.5 mr-1.5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                    Reset to Recommended
                </button>
            </form>
        </div>
    </div>

    <!-- Recommendations & Safety Notice -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-gray-50 border border-gray-200 rounded-xl p-4 flex items-start space-x-3">
            <div class="w-7 h-7 rounded-lg bg-emerald-100 text-emerald-600 flex items-center justify-center flex-shrink-0 mt-0.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            </div>
            <div>
                <h5 class="text-xs font-bold text-navy">Private Endpoints Protected</h5>
                <p class="text-[11px] text-gray-500 mt-0.5">Admin, dashboard, cart, checkout, login, and register URLs are disallowing crawler indexation.</p>
            </div>
        </div>

        <div class="bg-gray-50 border border-gray-200 rounded-xl p-4 flex items-start space-x-3">
            <div class="w-7 h-7 rounded-lg bg-cyan/20 text-navy flex items-center justify-center flex-shrink-0 mt-0.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
            </div>
            <div>
                <h5 class="text-xs font-bold text-navy">Sitemap Link Included</h5>
                <p class="text-[11px] text-gray-500 mt-0.5">The <code class="text-[10px] font-mono">Sitemap:</code> directive is auto-referenced at the bottom of the file.</p>
            </div>
        </div>

        <div class="bg-gray-50 border border-gray-200 rounded-xl p-4 flex items-start space-x-3">
            <div class="w-7 h-7 rounded-lg bg-amber-100 text-amber-600 flex items-center justify-center flex-shrink-0 mt-0.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            </div>
            <div>
                <h5 class="text-xs font-bold text-navy">Safe Syntax Validation</h5>
                <p class="text-[11px] text-gray-500 mt-0.5">Avoid placing <code class="text-[10px] font-mono">Disallow: /</code> which would block the entire public website.</p>
            </div>
        </div>
    </div>

    <!-- Robots Editor Form -->
    <form action="{{ route('admin.seo.robots.save') }}" method="POST" class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
        @csrf
        <div class="px-6 py-4 border-b border-gray-150 bg-gray-50/70 flex items-center justify-between">
            <div class="flex items-center space-x-2">
                <span class="w-3 h-3 rounded-full bg-red-400 inline-block"></span>
                <span class="w-3 h-3 rounded-full bg-yellow-400 inline-block"></span>
                <span class="w-3 h-3 rounded-full bg-green-400 inline-block"></span>
                <span class="text-xs font-mono text-gray-600 font-bold ml-2">public/robots.txt</span>
            </div>
            <span class="text-[11px] text-gray-500 font-mono">UTF-8 Plain Text</span>
        </div>

        <div class="p-6">
            <label for="robots_content" class="sr-only">Robots.txt Content</label>
            <textarea name="robots_content" id="robots_content" rows="18" class="w-full font-mono text-xs text-gray-900 bg-gray-900 text-gray-100 p-4 rounded-lg focus:ring-2 focus:ring-cyan focus:outline-none leading-relaxed selection:bg-cyan selection:text-navy" spellcheck="false">{{ old('robots_content', $robotsContent) }}</textarea>
            
            <div class="mt-4 flex items-center justify-between pt-4 border-t border-gray-100">
                <div class="text-xs text-gray-500 flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-emerald-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                    <span>Syntax validated. Changes take effect on next crawler visit.</span>
                </div>
                <button type="submit" class="inline-flex items-center px-5 py-2.5 text-xs font-bold text-white bg-navy hover:bg-gray-800 rounded-lg shadow-sm transition gap-2">
                    <svg class="w-4 h-4 text-cyan" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    Save &amp; Sync robots.txt
                </button>
            </div>
        </div>
    </form>
</div>
