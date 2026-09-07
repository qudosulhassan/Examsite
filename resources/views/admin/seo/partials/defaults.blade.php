<div class="space-y-6" x-data="{
    title: '{{ addslashes($settings['default_seo_title'] ?? config('seo.defaults.title')) }}',
    description: '{{ addslashes($settings['default_meta_description'] ?? config('seo.defaults.description')) }}',
    siteName: '{{ addslashes($settings['site_name'] ?? 'Exam Topics Base') }}',
    separator: '{{ $settings['seo_title_separator'] ?? '|' }}'
}">
    <form action="{{ route('admin.seo.update') }}" method="POST" class="space-y-6">
        @csrf
        <input type="hidden" name="active_tab" value="defaults">

        <div class="bg-white border border-gray-200 rounded-xl p-6 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="space-y-1">
                <div class="flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-cyan animate-pulse"></span>
                    <h4 class="text-base font-extrabold text-navy">Global SEO Fallbacks &amp; Templates</h4>
                    <span class="px-2 py-0.5 text-[10px] font-bold bg-cyan/10 text-navy border border-cyan/30 rounded-full">Default Meta Values</span>
                </div>
                <p class="text-xs text-gray-500">
                    Fallback titles, descriptions, and share assets applied when specific exam or blog posts don't have custom metadata.
                </p>
            </div>
        </div>

        <!-- Live Google SERP Preview Card -->
        <div class="bg-white border border-gray-200 rounded-xl p-6 shadow-sm space-y-3">
            <div class="flex items-center justify-between">
                <span class="text-[10px] uppercase font-bold tracking-wider text-gray-400">Live Google Search Result Preview</span>
                <span class="text-[11px] text-gray-500">Desktop &amp; Mobile SERP</span>
            </div>

            <div class="bg-[#f8f9fa] border border-gray-200 rounded-xl p-5 max-w-2xl space-y-1.5 font-sans">
                <div class="flex items-center gap-2 text-xs text-[#202124]">
                    <span class="w-4 h-4 rounded-full bg-navy text-white text-[9px] font-bold flex items-center justify-center">E</span>
                    <span class="truncate font-medium text-[#4d5156]">{{ $settings['site_url'] ?? 'http://127.0.0.1:8000' }}</span>
                </div>
                <h4 class="text-base text-[#1a0dab] hover:underline font-medium cursor-pointer leading-snug" x-text="title || (siteName + ' ' + separator + ' Pass Your IT Certification Exam First Attempt')"></h4>
                <p class="text-xs text-[#4d5156] leading-relaxed line-clamp-2" x-text="description || 'Prepare for any IT certification exam with verified practice questions and study materials.'"></p>
            </div>
        </div>

        <!-- Form Inputs -->
        <div class="bg-white border border-gray-200 rounded-xl p-6 shadow-sm space-y-6">
            <h5 class="text-xs font-bold text-navy uppercase tracking-wider border-b border-gray-150 pb-3">Title &amp; Meta Defaults</h5>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="md:col-span-2">
                    <label for="seo_title_template" class="block text-xs font-bold text-navy mb-1 uppercase tracking-wide">Title Template</label>
                    <input type="text" name="seo_title_template" id="seo_title_template" value="{{ old('seo_title_template', $settings['seo_title_template'] ?? '%title% %separator% %site_name%') }}" class="w-full text-xs font-mono border-gray-300 rounded-lg focus:border-cyan focus:ring-cyan">
                    <p class="text-[11px] text-gray-400 mt-1">Placeholders: <code class="font-mono text-navy">%title%</code>, <code class="font-mono text-navy">%separator%</code>, <code class="font-mono text-navy">%site_name%</code></p>
                </div>

                <div>
                    <label for="seo_title_separator" class="block text-xs font-bold text-navy mb-1 uppercase tracking-wide">Brand Separator</label>
                    <select name="seo_title_separator" id="seo_title_separator" x-model="separator" class="w-full text-xs border-gray-300 rounded-lg focus:border-cyan focus:ring-cyan font-bold">
                        <option value="|">| (Pipe)</option>
                        <option value="-">- (Hyphen)</option>
                        <option value="•">• (Bullet)</option>
                        <option value="—">— (Em Dash)</option>
                    </select>
                </div>
            </div>

            <!-- Default SEO Title -->
            <div>
                <div class="flex items-center justify-between mb-1">
                    <label for="default_seo_title" class="block text-xs font-bold text-navy uppercase tracking-wide">Default Homepage SEO Title</label>
                    <span class="text-[10px] text-gray-400 font-mono"><span x-text="title.length"></span> / 60 chars</span>
                </div>
                <input type="text" name="default_seo_title" id="default_seo_title" x-model="title" value="{{ old('default_seo_title', $settings['default_seo_title'] ?? config('seo.defaults.title')) }}" class="w-full text-xs border-gray-300 rounded-lg focus:border-cyan focus:ring-cyan font-medium">
            </div>

            <!-- Default Meta Description -->
            <div>
                <div class="flex items-center justify-between mb-1">
                    <label for="default_meta_description" class="block text-xs font-bold text-navy uppercase tracking-wide">Default Meta Description</label>
                    <span class="text-[10px] text-gray-400 font-mono"><span x-text="description.length"></span> / 160 chars</span>
                </div>
                <textarea name="default_meta_description" id="default_meta_description" x-model="description" rows="3" class="w-full text-xs border-gray-300 rounded-lg focus:border-cyan focus:ring-cyan leading-relaxed">{{ old('default_meta_description', $settings['default_meta_description'] ?? config('seo.defaults.description')) }}</textarea>
            </div>

            <!-- Default Keywords -->
            <div>
                <label for="default_meta_keywords" class="block text-xs font-bold text-navy mb-1 uppercase tracking-wide">Default Meta Keywords</label>
                <input type="text" name="default_meta_keywords" id="default_meta_keywords" value="{{ old('default_meta_keywords', $settings['default_meta_keywords'] ?? config('seo.defaults.keywords')) }}" class="w-full text-xs border-gray-300 rounded-lg focus:border-cyan focus:ring-cyan">
            </div>

            <div class="flex justify-end pt-4 border-t border-gray-150">
                <button type="submit" class="inline-flex items-center px-5 py-2.5 text-xs font-bold text-white bg-navy hover:bg-gray-800 rounded-lg shadow-sm transition gap-2">
                    <svg class="w-4 h-4 text-cyan" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    Save SEO Defaults
                </button>
            </div>
        </div>
    </form>
</div>
