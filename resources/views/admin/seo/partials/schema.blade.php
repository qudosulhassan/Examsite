<div class="space-y-6" x-data="{ selectedSchema: 'organization' }">
    <form action="{{ route('admin.seo.update') }}" method="POST" class="space-y-6">
        @csrf
        <input type="hidden" name="active_tab" value="schema">

        <!-- Header Controls -->
        <div class="bg-white border border-gray-200 rounded-xl p-6 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="space-y-1">
                <div class="flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-cyan animate-pulse"></span>
                    <h4 class="text-base font-extrabold text-navy">Structured Data &amp; Schema Markup</h4>
                    <span class="px-2 py-0.5 text-[10px] font-bold bg-cyan/10 text-navy border border-cyan/30 rounded-full">JSON-LD Standard</span>
                </div>
                <p class="text-xs text-gray-500">
                    Generates Google-compliant rich snippet markup for higher search visibility, sitelinks searchboxes, and product stars.
                </p>
            </div>

            <!-- Master Toggle -->
            <div class="flex items-center space-x-3 bg-gray-50 px-4 py-2 rounded-xl border border-gray-200">
                <label for="seo_schema_master_enabled" class="text-xs font-bold text-navy">Master Schema Switch</label>
                <input type="hidden" name="seo_schema_master_enabled" value="0">
                <input type="checkbox" name="seo_schema_master_enabled" id="seo_schema_master_enabled" value="1" {{ ($settings['seo_schema_master_enabled'] ?? '1') === '1' ? 'checked' : '' }} class="rounded border-gray-300 text-cyan focus:ring-cyan h-4 w-4">
            </div>
        </div>

        <!-- Schema Modules Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <!-- Organization Schema -->
            <div class="bg-white border border-gray-200 rounded-xl p-5 shadow-sm space-y-3">
                <div class="flex items-start justify-between">
                    <div>
                        <span class="text-[10px] uppercase font-bold text-cyan tracking-wider">Global Identity</span>
                        <h5 class="text-sm font-bold text-navy">Organization Schema</h5>
                        <p class="text-xs text-gray-500 mt-0.5">Emits site name, brand logo, contact points, and linked social accounts.</p>
                    </div>
                    <input type="hidden" name="seo_schema_organization_enabled" value="0">
                    <input type="checkbox" name="seo_schema_organization_enabled" value="1" {{ ($settings['seo_schema_organization_enabled'] ?? '1') === '1' ? 'checked' : '' }} class="rounded border-gray-300 text-cyan focus:ring-cyan h-4 w-4 mt-1">
                </div>
            </div>

            <!-- WebSite Schema & Sitelinks Searchbox -->
            <div class="bg-white border border-gray-200 rounded-xl p-5 shadow-sm space-y-3">
                <div class="flex items-start justify-between">
                    <div>
                        <span class="text-[10px] uppercase font-bold text-cyan tracking-wider">Search Integration</span>
                        <h5 class="text-sm font-bold text-navy">WebSite + Sitelinks Searchbox</h5>
                        <p class="text-xs text-gray-500 mt-0.5">Enables Google SearchAction so users can search exams right from Google results.</p>
                    </div>
                    <input type="hidden" name="seo_schema_website_enabled" value="0">
                    <input type="checkbox" name="seo_schema_website_enabled" value="1" {{ ($settings['seo_schema_website_enabled'] ?? '1') === '1' ? 'checked' : '' }} class="rounded border-gray-300 text-cyan focus:ring-cyan h-4 w-4 mt-1">
                </div>
            </div>

            <!-- BreadcrumbList Schema -->
            <div class="bg-white border border-gray-200 rounded-xl p-5 shadow-sm space-y-3">
                <div class="flex items-start justify-between">
                    <div>
                        <span class="text-[10px] uppercase font-bold text-cyan tracking-wider">Hierarchy</span>
                        <h5 class="text-sm font-bold text-navy">BreadcrumbList Schema</h5>
                        <p class="text-xs text-gray-500 mt-0.5">Formats URL paths into clear breadcrumb navigational trails on Google SERPs.</p>
                    </div>
                    <input type="hidden" name="seo_schema_breadcrumbs_enabled" value="0">
                    <input type="checkbox" name="seo_schema_breadcrumbs_enabled" value="1" {{ ($settings['seo_schema_breadcrumbs_enabled'] ?? '1') === '1' ? 'checked' : '' }} class="rounded border-gray-300 text-cyan focus:ring-cyan h-4 w-4 mt-1">
                </div>
            </div>

            <!-- Product & Offer Schema -->
            <div class="bg-white border border-gray-200 rounded-xl p-5 shadow-sm space-y-3">
                <div class="flex items-start justify-between">
                    <div>
                        <span class="text-[10px] uppercase font-bold text-cyan tracking-wider">eCommerce &amp; Guides</span>
                        <h5 class="text-sm font-bold text-navy">Product &amp; Offer Schema</h5>
                        <p class="text-xs text-gray-500 mt-0.5">Exposes exam study guides, pricing, availability, and aggregate star ratings.</p>
                    </div>
                    <input type="hidden" name="seo_schema_product_enabled" value="0">
                    <input type="checkbox" name="seo_schema_product_enabled" value="1" {{ ($settings['seo_schema_product_enabled'] ?? '1') === '1' ? 'checked' : '' }} class="rounded border-gray-300 text-cyan focus:ring-cyan h-4 w-4 mt-1">
                </div>
            </div>

            <!-- Course / Certification Schema -->
            <div class="bg-white border border-gray-200 rounded-xl p-5 shadow-sm space-y-3">
                <div class="flex items-start justify-between">
                    <div>
                        <span class="text-[10px] uppercase font-bold text-cyan tracking-wider">Education</span>
                        <h5 class="text-sm font-bold text-navy">Course / Certification Schema</h5>
                        <p class="text-xs text-gray-500 mt-0.5">Marks up IT certification exam pages as professional educational programs.</p>
                    </div>
                    <input type="hidden" name="seo_schema_course_enabled" value="0">
                    <input type="checkbox" name="seo_schema_course_enabled" value="1" {{ ($settings['seo_schema_course_enabled'] ?? '1') === '1' ? 'checked' : '' }} class="rounded border-gray-300 text-cyan focus:ring-cyan h-4 w-4 mt-1">
                </div>
            </div>

            <!-- Article / BlogPosting Schema -->
            <div class="bg-white border border-gray-200 rounded-xl p-5 shadow-sm space-y-3">
                <div class="flex items-start justify-between">
                    <div>
                        <span class="text-[10px] uppercase font-bold text-cyan tracking-wider">Editorial Content</span>
                        <h5 class="text-sm font-bold text-navy">Article &amp; BlogPosting Schema</h5>
                        <p class="text-xs text-gray-500 mt-0.5">Embeds author, publisher, and timestamp metadata for blog posts.</p>
                    </div>
                    <input type="hidden" name="seo_schema_article_enabled" value="0">
                    <input type="checkbox" name="seo_schema_article_enabled" value="1" {{ ($settings['seo_schema_article_enabled'] ?? '1') === '1' ? 'checked' : '' }} class="rounded border-gray-300 text-cyan focus:ring-cyan h-4 w-4 mt-1">
                </div>
            </div>
        </div>

        <div class="flex justify-end">
            <button type="submit" class="inline-flex items-center px-5 py-2.5 text-xs font-bold text-white bg-navy hover:bg-gray-800 rounded-lg shadow-sm transition gap-2">
                <svg class="w-4 h-4 text-cyan" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                Save Schema Preferences
            </button>
        </div>
    </form>

    <!-- Live JSON-LD Previewer -->
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-150 bg-gray-50/70 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="flex items-center space-x-2">
                <h5 class="text-xs font-bold text-navy uppercase tracking-wider">Live JSON-LD Schema Inspector</h5>
                <span class="text-[10px] bg-gray-200 text-gray-700 px-2 py-0.5 rounded font-mono">application/ld+json</span>
            </div>

            <div class="flex items-center space-x-2">
                <select x-model="selectedSchema" class="text-xs font-semibold border-gray-300 rounded-lg focus:ring-cyan focus:border-cyan py-1.5 pl-2.5 pr-8">
                    <option value="organization">Organization Schema</option>
                    <option value="website">WebSite + SearchAction</option>
                    <option value="breadcrumbs">BreadcrumbList</option>
                    <option value="product">Product &amp; Offer</option>
                    <option value="course">Course &amp; Certification</option>
                    <option value="article">BlogPosting / Article</option>
                </select>

                <a href="https://search.google.com/test/rich-results" target="_blank" class="inline-flex items-center px-2.5 py-1.5 text-xs font-bold text-navy hover:text-cyan bg-white border border-gray-300 rounded-lg shadow-sm transition gap-1">
                    <span>Google Rich Results Test</span>
                    <svg class="w-3 h-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                </a>
            </div>
        </div>

        <div class="p-6">
            <div x-show="selectedSchema === 'organization'">
                <pre class="bg-gray-900 text-cyan p-4 rounded-lg font-mono text-xs overflow-x-auto leading-relaxed">{{ $schemaPreviews['organization'] }}</pre>
            </div>
            <div x-show="selectedSchema === 'website'">
                <pre class="bg-gray-900 text-cyan p-4 rounded-lg font-mono text-xs overflow-x-auto leading-relaxed">{{ $schemaPreviews['website'] }}</pre>
            </div>
            <div x-show="selectedSchema === 'breadcrumbs'">
                <pre class="bg-gray-900 text-cyan p-4 rounded-lg font-mono text-xs overflow-x-auto leading-relaxed">{{ $schemaPreviews['breadcrumbs'] }}</pre>
            </div>
            <div x-show="selectedSchema === 'product'">
                <pre class="bg-gray-900 text-cyan p-4 rounded-lg font-mono text-xs overflow-x-auto leading-relaxed">{{ $schemaPreviews['product'] }}</pre>
            </div>
            <div x-show="selectedSchema === 'course'">
                <pre class="bg-gray-900 text-cyan p-4 rounded-lg font-mono text-xs overflow-x-auto leading-relaxed">{{ $schemaPreviews['course'] }}</pre>
            </div>
            <div x-show="selectedSchema === 'article'">
                <pre class="bg-gray-900 text-cyan p-4 rounded-lg font-mono text-xs overflow-x-auto leading-relaxed">{{ $schemaPreviews['article'] }}</pre>
            </div>
        </div>
    </div>
</div>
