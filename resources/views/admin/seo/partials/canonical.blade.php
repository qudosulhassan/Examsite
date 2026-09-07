<div class="space-y-6">
    <form action="{{ route('admin.seo.update') }}" method="POST" class="space-y-6">
        @csrf
        <input type="hidden" name="active_tab" value="canonical">

        <div class="bg-white border border-gray-200 rounded-xl p-6 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="space-y-1">
                <div class="flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-cyan animate-pulse"></span>
                    <h4 class="text-base font-extrabold text-navy">Canonical URLs &amp; Duplicate Protection</h4>
                    <span class="px-2 py-0.5 text-[10px] font-bold bg-cyan/10 text-navy border border-cyan/30 rounded-full">RFC 6596 rel="canonical"</span>
                </div>
                <p class="text-xs text-gray-500">
                    Defines authoritative page URLs to prevent duplicate content penalties across tracking query strings, SSL variants, and paginations.
                </p>
            </div>
        </div>

        <div class="bg-white border border-gray-200 rounded-xl p-6 shadow-sm space-y-6">
            <h5 class="text-xs font-bold text-navy uppercase tracking-wider border-b border-gray-150 pb-3">Global Canonical Settings</h5>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Force HTTPS in Canonicals -->
                <div class="bg-gray-50 p-4 rounded-xl border border-gray-200 space-y-2">
                    <div class="flex items-center justify-between">
                        <label for="seo_canonical_force_https" class="text-xs font-bold text-navy">Force HTTPS in Canonicals</label>
                        <input type="hidden" name="seo_canonical_force_https" value="0">
                        <input type="checkbox" name="seo_canonical_force_https" id="seo_canonical_force_https" value="1" {{ ($settings['seo_canonical_force_https'] ?? '1') === '1' ? 'checked' : '' }} class="rounded border-gray-300 text-cyan focus:ring-cyan h-4 w-4">
                    </div>
                    <p class="text-[11px] text-gray-500">Ensures all generated &lt;link rel="canonical"&gt; tags use https:// protocol, even behind reverse proxies.</p>
                </div>

                <!-- Strip Trailing Slashes -->
                <div class="bg-gray-50 p-4 rounded-xl border border-gray-200 space-y-2">
                    <div class="flex items-center justify-between">
                        <label for="seo_canonical_strip_slash" class="text-xs font-bold text-navy">Strip Trailing Slash</label>
                        <input type="hidden" name="seo_canonical_strip_slash" value="0">
                        <input type="checkbox" name="seo_canonical_strip_slash" id="seo_canonical_strip_slash" value="1" {{ ($settings['seo_canonical_strip_slash'] ?? '1') === '1' ? 'checked' : '' }} class="rounded border-gray-300 text-cyan focus:ring-cyan h-4 w-4">
                    </div>
                    <p class="text-[11px] text-gray-500">Normalizes URLs such as /exams/cisco/200-301/ to clean /exams/cisco/200-301.</p>
                </div>
            </div>

            <!-- Custom Overrides Guide -->
            <div class="bg-navy/5 border border-navy/10 rounded-xl p-4.5 space-y-2">
                <div class="flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-navy"></span>
                    <h6 class="text-xs font-bold text-navy">Individual Page Overrides</h6>
                </div>
                <p class="text-xs text-gray-600 leading-relaxed">
                    You can override the canonical URL for any specific exam or blog post directly within its edit screen (e.g. syndicated blog posts pointing back to external original publishers). If no override is provided, the engine automatically calculates the canonical URL cleanly.
                </p>
            </div>

            <div class="flex justify-end pt-4 border-t border-gray-150">
                <button type="submit" class="inline-flex items-center px-5 py-2.5 text-xs font-bold text-white bg-navy hover:bg-gray-800 rounded-lg shadow-sm transition gap-2">
                    <svg class="w-4 h-4 text-cyan" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    Save Canonical Preferences
                </button>
            </div>
        </div>
    </form>
</div>
