<div class="space-y-6">
    <form action="{{ route('admin.seo.update') }}" method="POST" class="space-y-6">
        @csrf
        <input type="hidden" name="active_tab" value="search_engines">

        <div class="bg-white border border-gray-200 rounded-xl p-6 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="space-y-1">
                <div class="flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-cyan animate-pulse"></span>
                    <h4 class="text-base font-extrabold text-navy">Search Engine Verification &amp; Indexing Tools</h4>
                    <span class="px-2 py-0.5 text-[10px] font-bold bg-cyan/10 text-navy border border-cyan/30 rounded-full">Google &amp; Bing Webmasters</span>
                </div>
                <p class="text-xs text-gray-500">
                    Verify website ownership with major search engines to unlock keyword analytics, submit XML sitemaps, and request instant crawling.
                </p>
            </div>
        </div>

        <!-- Quick Diagnostic Launchpad -->
        <div class="bg-white border border-gray-200 rounded-xl p-6 shadow-sm space-y-4">
            <h5 class="text-xs font-bold text-navy uppercase tracking-wider border-b border-gray-150 pb-3">Official Search Engine Tools Launchpad</h5>

            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3">
                <a href="https://search.google.com/search-console" target="_blank" class="p-3.5 bg-gray-50 hover:bg-gray-100 rounded-xl border border-gray-200 transition text-center space-y-1 group">
                    <div class="w-7 h-7 mx-auto rounded-lg bg-blue-100 text-blue-600 flex items-center justify-center font-black text-xs">G</div>
                    <div class="text-xs font-bold text-navy group-hover:text-cyan transition">Search Console</div>
                    <div class="text-[10px] text-gray-400">Google Performance</div>
                </a>

                <a href="https://search.google.com/test/rich-results" target="_blank" class="p-3.5 bg-gray-50 hover:bg-gray-100 rounded-xl border border-gray-200 transition text-center space-y-1 group">
                    <div class="w-7 h-7 mx-auto rounded-lg bg-emerald-100 text-emerald-600 flex items-center justify-center font-black text-xs">&lt;/&gt;</div>
                    <div class="text-xs font-bold text-navy group-hover:text-cyan transition">Rich Results Test</div>
                    <div class="text-[10px] text-gray-400">Schema Validator</div>
                </a>

                <a href="https://pagespeed.web.dev/" target="_blank" class="p-3.5 bg-gray-50 hover:bg-gray-100 rounded-xl border border-gray-200 transition text-center space-y-1 group">
                    <div class="w-7 h-7 mx-auto rounded-lg bg-amber-100 text-amber-600 flex items-center justify-center font-black text-xs">&lightning;</div>
                    <div class="text-xs font-bold text-navy group-hover:text-cyan transition">PageSpeed Insights</div>
                    <div class="text-[10px] text-gray-400">Core Web Vitals</div>
                </a>

                <a href="https://www.bing.com/webmasters" target="_blank" class="p-3.5 bg-gray-50 hover:bg-gray-100 rounded-xl border border-gray-200 transition text-center space-y-1 group">
                    <div class="w-7 h-7 mx-auto rounded-lg bg-teal-100 text-teal-600 flex items-center justify-center font-black text-xs">B</div>
                    <div class="text-xs font-bold text-navy group-hover:text-cyan transition">Bing Webmaster</div>
                    <div class="text-[10px] text-gray-400">Bing &amp; Yahoo SEO</div>
                </a>

                <a href="{{ url('/sitemap.xml') }}" target="_blank" class="p-3.5 bg-gray-50 hover:bg-gray-100 rounded-xl border border-gray-200 transition text-center space-y-1 group">
                    <div class="w-7 h-7 mx-auto rounded-lg bg-purple-100 text-purple-600 flex items-center justify-center font-black text-xs">XML</div>
                    <div class="text-xs font-bold text-navy group-hover:text-cyan transition">Sitemap Feed</div>
                    <div class="text-[10px] text-gray-400">Inspect Output</div>
                </a>
            </div>
        </div>

        <!-- Verification Meta Tag Inputs -->
        <div class="bg-white border border-gray-200 rounded-xl p-6 shadow-sm space-y-6">
            <h5 class="text-xs font-bold text-navy uppercase tracking-wider border-b border-gray-150 pb-3">Search Engine Verification Codes</h5>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Google Site Verification -->
                <div>
                    <label for="seo_gsc_verification" class="block text-xs font-bold text-navy mb-1 uppercase tracking-wide">Google Search Console Verification Token</label>
                    <input type="text" name="seo_gsc_verification" id="seo_gsc_verification" value="{{ old('seo_gsc_verification', $settings['seo_gsc_verification'] ?? config('seo.verification.google_search_console')) }}" placeholder="e.g. 4vN1j9G_x9z_abcdefgh123456" class="w-full text-xs font-mono border-gray-300 rounded-lg focus:border-cyan focus:ring-cyan">
                    <p class="text-[11px] text-gray-400 mt-1">Will generate: &lt;meta name="google-site-verification" content="..."&gt;</p>
                </div>

                <!-- Bing Webmaster Verification -->
                <div>
                    <label for="seo_bing_verification" class="block text-xs font-bold text-navy mb-1 uppercase tracking-wide">Bing Webmaster Tools Verification Token</label>
                    <input type="text" name="seo_bing_verification" id="seo_bing_verification" value="{{ old('seo_bing_verification', $settings['seo_bing_verification'] ?? '') }}" placeholder="e.g. 892348ABCDEF1234567890" class="w-full text-xs font-mono border-gray-300 rounded-lg focus:border-cyan focus:ring-cyan">
                    <p class="text-[11px] text-gray-400 mt-1">Will generate: &lt;meta name="msvalidate.01" content="..."&gt;</p>
                </div>

                <!-- Yandex Verification -->
                <div>
                    <label for="seo_yandex_verification" class="block text-xs font-bold text-navy mb-1 uppercase tracking-wide">Yandex Verification Token</label>
                    <input type="text" name="seo_yandex_verification" id="seo_yandex_verification" value="{{ old('seo_yandex_verification', $settings['seo_yandex_verification'] ?? '') }}" placeholder="e.g. a1b2c3d4e5f6g7h8" class="w-full text-xs font-mono border-gray-300 rounded-lg focus:border-cyan focus:ring-cyan">
                    <p class="text-[11px] text-gray-400 mt-1">Will generate: &lt;meta name="yandex-verification" content="..."&gt;</p>
                </div>

                <!-- Pinterest Verification -->
                <div>
                    <label for="seo_pinterest_verification" class="block text-xs font-bold text-navy mb-1 uppercase tracking-wide">Pinterest Domain Verification</label>
                    <input type="text" name="seo_pinterest_verification" id="seo_pinterest_verification" value="{{ old('seo_pinterest_verification', $settings['seo_pinterest_verification'] ?? '') }}" placeholder="e.g. 7f8a9b..." class="w-full text-xs font-mono border-gray-300 rounded-lg focus:border-cyan focus:ring-cyan">
                    <p class="text-[11px] text-gray-400 mt-1">Will generate: &lt;meta name="p:domain_verify" content="..."&gt;</p>
                </div>
            </div>

            <!-- Sitemap Submission Guide -->
            <div class="bg-gray-50 border border-gray-200 rounded-xl p-4.5 space-y-2">
                <div class="text-xs font-bold text-navy flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-cyan" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/></svg>
                    <span>How to Submit Your XML Sitemap to Google:</span>
                </div>
                <ol class="list-decimal list-inside text-xs text-gray-600 space-y-1 pl-1">
                    <li>Log into <a href="https://search.google.com/search-console" target="_blank" class="text-navy font-bold hover:underline">Google Search Console</a>.</li>
                    <li>Select your property and click <strong>Sitemaps</strong> in the left sidebar.</li>
                    <li>Under "Add a new sitemap", enter <code class="bg-white border px-1 py-0.5 rounded font-mono text-[11px] text-navy">sitemap.xml</code> and click <strong>Submit</strong>.</li>
                    <li>Google will fetch the sitemap automatically and index your URLs.</li>
                </ol>
            </div>

            <div class="flex justify-end pt-4 border-t border-gray-150">
                <button type="submit" class="inline-flex items-center px-5 py-2.5 text-xs font-bold text-white bg-navy hover:bg-gray-800 rounded-lg shadow-sm transition gap-2">
                    <svg class="w-4 h-4 text-cyan" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    Save Verification Tokens
                </button>
            </div>
        </div>
    </form>
</div>
