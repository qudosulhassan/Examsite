<div class="space-y-6">
    <form action="{{ route('admin.seo.update') }}" method="POST" class="space-y-6">
        @csrf
        <input type="hidden" name="active_tab" value="meta_indexing">

        <div class="bg-white border border-gray-200 rounded-xl p-6 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="space-y-1">
                <div class="flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-cyan animate-pulse"></span>
                    <h4 class="text-base font-extrabold text-navy">Meta &amp; Indexing Directives</h4>
                    <span class="px-2 py-0.5 text-[10px] font-bold bg-cyan/10 text-navy border border-cyan/30 rounded-full">Global Directives</span>
                </div>
                <p class="text-xs text-gray-500">
                    Instruct search engine crawlers how to index, display snippets, and follow hyperlinks across the website.
                </p>
            </div>
        </div>

        <!-- Global Robots Directives Card -->
        <div class="bg-white border border-gray-200 rounded-xl p-6 shadow-sm space-y-6">
            <h5 class="text-xs font-bold text-navy uppercase tracking-wider border-b border-gray-150 pb-3">Primary Indexing Directives</h5>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="seo_robots_index" class="block text-xs font-bold text-navy mb-1.5 uppercase tracking-wide">Index Directive</label>
                    <select name="seo_robots_index" id="seo_robots_index" class="w-full text-xs border-gray-300 rounded-lg focus:border-cyan focus:ring-cyan font-medium">
                        <option value="index" {{ ($settings['seo_robots_index'] ?? 'index') === 'index' ? 'selected' : '' }}>index (Allow pages to appear in search results - Recommended)</option>
                        <option value="noindex" {{ ($settings['seo_robots_index'] ?? '') === 'noindex' ? 'selected' : '' }}>noindex (Block all search indexing - Staging / Maintenance)</option>
                    </select>
                </div>

                <div>
                    <label for="seo_robots_follow" class="block text-xs font-bold text-navy mb-1.5 uppercase tracking-wide">Follow Directive</label>
                    <select name="seo_robots_follow" id="seo_robots_follow" class="w-full text-xs border-gray-300 rounded-lg focus:border-cyan focus:ring-cyan font-medium">
                        <option value="follow" {{ ($settings['seo_robots_follow'] ?? 'follow') === 'follow' ? 'selected' : '' }}>follow (Allow search spiders to follow on-page links - Recommended)</option>
                        <option value="nofollow" {{ ($settings['seo_robots_follow'] ?? '') === 'nofollow' ? 'selected' : '' }}>nofollow (Instruct crawlers not to follow internal or external links)</option>
                    </select>
                </div>
            </div>

            <!-- Advanced Robots Directives -->
            <div class="space-y-3 pt-4 border-t border-gray-150">
                <h6 class="text-xs font-bold text-navy uppercase tracking-wide">Advanced Crawler Permissions</h6>
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                    <label class="flex items-center space-x-3 bg-gray-50 p-3 rounded-lg border border-gray-200 cursor-pointer hover:bg-gray-100/80 transition">
                        <input type="hidden" name="seo_robots_noarchive" value="0">
                        <input type="checkbox" name="seo_robots_noarchive" value="1" {{ ($settings['seo_robots_noarchive'] ?? '0') === '1' ? 'checked' : '' }} class="rounded border-gray-300 text-cyan focus:ring-cyan h-4 w-4">
                        <span class="text-xs font-semibold text-gray-700">noarchive <span class="text-[10px] text-gray-400 block font-normal">Do not show cached link in SERP</span></span>
                    </label>

                    <label class="flex items-center space-x-3 bg-gray-50 p-3 rounded-lg border border-gray-200 cursor-pointer hover:bg-gray-100/80 transition">
                        <input type="hidden" name="seo_robots_nosnippet" value="0">
                        <input type="checkbox" name="seo_robots_nosnippet" value="1" {{ ($settings['seo_robots_nosnippet'] ?? '0') === '1' ? 'checked' : '' }} class="rounded border-gray-300 text-cyan focus:ring-cyan h-4 w-4">
                        <span class="text-xs font-semibold text-gray-700">nosnippet <span class="text-[10px] text-gray-400 block font-normal">Do not show text snippet or video preview</span></span>
                    </label>

                    <label class="flex items-center space-x-3 bg-gray-50 p-3 rounded-lg border border-gray-200 cursor-pointer hover:bg-gray-100/80 transition">
                        <input type="hidden" name="seo_robots_max_image_preview" value="0">
                        <input type="checkbox" name="seo_robots_max_image_preview" value="1" {{ ($settings['seo_robots_max_image_preview'] ?? '1') === '1' ? 'checked' : '' }} class="rounded border-gray-300 text-cyan focus:ring-cyan h-4 w-4">
                        <span class="text-xs font-semibold text-gray-700">max-image-preview:large <span class="text-[10px] text-gray-400 block font-normal">Enable large image rich previews in Google Discover</span></span>
                    </label>
                </div>
            </div>

            <!-- Live Tag Preview -->
            <div class="bg-gray-900 text-cyan p-4 rounded-xl font-mono text-xs space-y-1">
                <span class="text-gray-400 text-[10px] uppercase tracking-wider block font-bold">Generated Output Tag</span>
                <code>&lt;meta name="robots" content="{{ app(\App\Services\TechnicalSeoService::class)->getRobotsMetaDirective() }}"&gt;</code>
            </div>
        </div>

        <!-- Social Meta (Open Graph & Twitter Cards) -->
        <div class="bg-white border border-gray-200 rounded-xl p-6 shadow-sm space-y-6">
            <h5 class="text-xs font-bold text-navy uppercase tracking-wider border-b border-gray-150 pb-3">Open Graph &amp; Twitter Card Defaults</h5>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="default_og_title" class="block text-xs font-bold text-navy mb-1 uppercase tracking-wide">Default Social Share Title</label>
                    <input type="text" name="default_og_title" id="default_og_title" value="{{ old('default_og_title', $settings['default_og_title'] ?? '') }}" placeholder="{{ $settings['site_name'] ?? 'Exam Topics Base' }}" class="w-full text-xs border-gray-300 rounded-lg focus:border-cyan focus:ring-cyan">
                </div>

                <div>
                    <label for="seo_twitter_card" class="block text-xs font-bold text-navy mb-1 uppercase tracking-wide">Twitter Card Format</label>
                    <select name="seo_twitter_card" id="seo_twitter_card" class="w-full text-xs border-gray-300 rounded-lg focus:border-cyan focus:ring-cyan">
                        <option value="summary_large_image" {{ ($settings['seo_twitter_card'] ?? 'summary_large_image') === 'summary_large_image' ? 'selected' : '' }}>summary_large_image (High-impact large social preview - Recommended)</option>
                        <option value="summary" {{ ($settings['seo_twitter_card'] ?? '') === 'summary' ? 'selected' : '' }}>summary (Compact square image preview)</option>
                    </select>
                </div>

                <div class="md:col-span-2">
                    <label for="default_og_description" class="block text-xs font-bold text-navy mb-1 uppercase tracking-wide">Default Social Description</label>
                    <textarea name="default_og_description" id="default_og_description" rows="2" class="w-full text-xs border-gray-300 rounded-lg focus:border-cyan focus:ring-cyan">{{ old('default_og_description', $settings['default_og_description'] ?? '') }}</textarea>
                </div>

                <div>
                    <label for="seo_facebook_app_id" class="block text-xs font-bold text-navy mb-1 uppercase tracking-wide">Facebook App ID (Optional)</label>
                    <input type="text" name="seo_facebook_app_id" id="seo_facebook_app_id" value="{{ old('seo_facebook_app_id', $settings['seo_facebook_app_id'] ?? '') }}" placeholder="123456789012345" class="w-full text-xs border-gray-300 rounded-lg focus:border-cyan focus:ring-cyan">
                </div>
            </div>

            <div class="flex justify-end pt-4 border-t border-gray-150">
                <button type="submit" class="inline-flex items-center px-5 py-2.5 text-xs font-bold text-white bg-navy hover:bg-gray-800 rounded-lg shadow-sm transition gap-2">
                    <svg class="w-4 h-4 text-cyan" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    Save Directives &amp; Social Meta
                </button>
            </div>
        </div>
    </form>
</div>
