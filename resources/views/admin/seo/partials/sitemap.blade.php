<div class="space-y-6">
    <!-- Sitemap Header Status Card -->
    <div class="bg-white border border-gray-200 rounded-xl p-6 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="space-y-1">
            <div class="flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
                <h4 class="text-base font-extrabold text-navy">Dynamic XML Sitemap</h4>
                <span class="px-2 py-0.5 text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 rounded-full">Standard 0.9 Protocol</span>
            </div>
            <p class="text-xs text-gray-500">
                Search engines use this XML feed to discover and index all public exams, vendor guides, blog articles, and certifications.
            </p>
            <div class="flex items-center gap-2 mt-2 pt-2 border-t border-gray-100">
                <span class="text-xs font-mono bg-gray-50 border border-gray-200 px-3 py-1 rounded-lg text-gray-700 select-all font-semibold">
                    {{ $sitemapData['sitemap_url'] }}
                </span>
                <a href="{{ $sitemapData['sitemap_url'] }}" target="_blank" class="inline-flex items-center px-2.5 py-1 text-xs font-bold text-navy hover:text-cyan bg-gray-100 hover:bg-gray-200 rounded-lg transition gap-1">
                    <span>View XML</span>
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                </a>
            </div>
        </div>

        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
            <div class="text-right sm:pr-4 sm:border-r border-gray-200">
                <div class="text-[10px] uppercase font-bold text-gray-400">Last Generated</div>
                <div class="text-xs font-bold text-navy">{{ $sitemapData['last_generated'] }}</div>
            </div>
            <form action="{{ route('admin.seo.sitemap.regenerate') }}" method="POST">
                @csrf
                <button type="submit" class="w-full sm:w-auto inline-flex items-center justify-center px-4 py-2.5 text-xs font-bold text-white bg-navy hover:bg-gray-800 rounded-lg shadow-sm transition gap-2">
                    <svg class="w-4 h-4 text-cyan" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                    Regenerate Sitemap
                </button>
            </form>
        </div>
    </div>

    <!-- Category Breakdown Metrics -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4">
        <div class="bg-white border border-gray-200 rounded-xl p-4 shadow-sm text-center">
            <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Total URLs</span>
            <div class="text-xl font-black text-navy mt-1">{{ $sitemapData['total'] }}</div>
            <span class="text-[10px] text-emerald-600 font-semibold">100% Crawlable</span>
        </div>

        <div class="bg-white border border-gray-200 rounded-xl p-4 shadow-sm text-center">
            <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Exams</span>
            <div class="text-xl font-black text-navy mt-1">{{ $sitemapData['exams_count'] }}</div>
            <span class="text-[10px] text-gray-400 font-semibold">Priority 0.9</span>
        </div>

        <div class="bg-white border border-gray-200 rounded-xl p-4 shadow-sm text-center">
            <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Vendors</span>
            <div class="text-xl font-black text-navy mt-1">{{ $sitemapData['vendors_count'] }}</div>
            <span class="text-[10px] text-gray-400 font-semibold">Priority 0.7</span>
        </div>

        <div class="bg-white border border-gray-200 rounded-xl p-4 shadow-sm text-center">
            <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Certifications</span>
            <div class="text-xl font-black text-navy mt-1">{{ $sitemapData['certs_count'] }}</div>
            <span class="text-[10px] text-gray-400 font-semibold">Priority 0.7</span>
        </div>

        <div class="bg-white border border-gray-200 rounded-xl p-4 shadow-sm text-center">
            <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Blog Posts</span>
            <div class="text-xl font-black text-navy mt-1">{{ $sitemapData['blog_count'] }}</div>
            <span class="text-[10px] text-gray-400 font-semibold">Priority 0.6</span>
        </div>

        <div class="bg-white border border-gray-200 rounded-xl p-4 shadow-sm text-center">
            <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Static Pages</span>
            <div class="text-xl font-black text-navy mt-1">{{ $sitemapData['static_count'] }}</div>
            <span class="text-[10px] text-gray-400 font-semibold">Priority 0.6 - 1.0</span>
        </div>
    </div>

    <!-- Exclusion Security Guarantee Banner -->
    <div class="bg-emerald-50 border border-emerald-200 rounded-xl p-4.5 flex items-start space-x-3 text-xs text-emerald-900">
        <svg class="w-5 h-5 text-emerald-600 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 1.944A11.954 11.954 0 012.166 5C2.056 5.649 2 6.319 2 7c0 5.225 3.34 9.67 8 11.317C14.66 16.67 18 12.225 18 7c0-.682-.057-1.35-.166-2.001A11.954 11.954 0 0110 1.944zM11 14a1 1 0 11-2 0 1 1 0 012 0zm0-7a1 1 0 10-2 0v4a1 1 0 102 0V7z" clip-rule="evenodd"/></svg>
        <div>
            <div class="font-bold text-emerald-950 mb-0.5">Strict Private URL Exclusions Verified</div>
            <p class="text-emerald-800 leading-relaxed">
                The XML sitemap strictly filters out administrative, private, user, authentication, and transaction paths. None of <code class="bg-emerald-100 px-1 py-0.5 rounded font-mono text-[11px]">/admin*</code>, <code class="bg-emerald-100 px-1 py-0.5 rounded font-mono text-[11px]">/dashboard*</code>, <code class="bg-emerald-100 px-1 py-0.5 rounded font-mono text-[11px]">/cart</code>, <code class="bg-emerald-100 px-1 py-0.5 rounded font-mono text-[11px]">/checkout</code>, <code class="bg-emerald-100 px-1 py-0.5 rounded font-mono text-[11px]">/login</code>, <code class="bg-emerald-100 px-1 py-0.5 rounded font-mono text-[11px]">/register</code>, or API webhooks are ever exposed.
            </p>
        </div>
    </div>

    <!-- Indexed URL Sample Table -->
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-150 bg-gray-50/60 flex items-center justify-between">
            <h5 class="text-xs font-bold text-navy uppercase tracking-wider">Indexed URLs Sample (First 25)</h5>
            <span class="text-[11px] text-gray-500 font-medium">Displaying 25 of {{ $sitemapData['total'] }} URLs</span>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-xs">
                <thead class="bg-gray-50 text-gray-600 font-bold uppercase tracking-wider">
                    <tr>
                        <th class="px-6 py-3 text-left">Location (URL)</th>
                        <th class="px-4 py-3 text-center">Change Frequency</th>
                        <th class="px-4 py-3 text-center">Priority</th>
                        <th class="px-4 py-3 text-right">Last Modified</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 font-mono">
                    @foreach(array_slice($sitemapData['urls'], 0, 25) as $u)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-6 py-2.5 text-navy font-sans truncate max-w-md font-medium">
                                <a href="{{ $u['loc'] }}" target="_blank" class="hover:text-cyan transition flex items-center gap-1.5">
                                    <span class="truncate">{{ $u['loc'] }}</span>
                                    <svg class="w-3 h-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                </a>
                            </td>
                            <td class="px-4 py-2.5 text-center text-gray-600">{{ $u['changefreq'] ?? 'weekly' }}</td>
                            <td class="px-4 py-2.5 text-center font-bold {{ (float)($u['priority'] ?? 0) >= 0.8 ? 'text-emerald-600' : 'text-gray-600' }}">{{ $u['priority'] ?? '0.5' }}</td>
                            <td class="px-4 py-2.5 text-right text-gray-500">{{ $u['lastmod'] ?? now()->format('Y-m-d') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
