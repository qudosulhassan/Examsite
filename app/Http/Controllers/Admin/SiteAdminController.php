<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Site;
use App\Models\SiteDomain;
use App\Models\Exam;
use App\Models\SiteExamOverlay;
use App\Models\Vendor;
use App\Models\Question;
use App\Models\Certification;
use App\Services\HtmlSanitizerService;
use App\Services\SiteContext;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class SiteAdminController extends Controller
{
    /**
     * Defense-in-depth: Ensure Site parameter matches this app's tenant ID,
     * unless this application is running as Central Core (Site ID = 1),
     * in which case the administrator has authority to manage all sites.
     */
    protected function assertSiteBelongsToApp(Site $site): void
    {
        $currentAppSiteId = (int)config('site.id', 1);
        if ($currentAppSiteId !== 1 && $site->id !== $currentAppSiteId) {
            abort(403, "Unauthorized access to site ID {$site->id}. This admin panel is restricted to Site ID {$currentAppSiteId}.");
        }

        // On Central Core (Site A), multi-site platform controls are restricted to Platform / Super Admin
        if ($currentAppSiteId === 1 && !auth()->user()?->isSuperAdmin()) {
            abort(403, 'Unauthorized access. Multi-site platform management is restricted to Platform Administrators.');
        }
    }

    public function index()
    {
        $currentAppSiteId = (int)config('site.id', 1);
        if ($currentAppSiteId !== 1) {
            return redirect()->route('admin.sites.edit', $currentAppSiteId);
        }

        if (!auth()->user()?->isSuperAdmin()) {
            abort(403, 'Unauthorized access. Websites Platform management is restricted to Platform Administrators.');
        }

        $sites = Site::with(['domains', 'examOverlays'])->withCount('domains')->get();
        return view('admin.sites.index', compact('sites'));
    }

    public function create()
    {
        $currentAppSiteId = (int)config('site.id', 1);
        if ($currentAppSiteId !== 1) {
            return redirect()->route('admin.sites.edit', $currentAppSiteId)->with('error', 'Creating new websites is reserved for the Central Core.');
        }

        if (!auth()->user()?->isSuperAdmin()) {
            abort(403, 'Unauthorized access. Websites Platform management is restricted to Platform Administrators.');
        }

        return view('admin.sites.create');
    }

    public function store(Request $request)
    {
        $currentAppSiteId = (int)config('site.id', 1);
        if ($currentAppSiteId !== 1) {
            return redirect()->route('admin.sites.edit', $currentAppSiteId);
        }

        if (!auth()->user()?->isSuperAdmin()) {
            abort(403, 'Unauthorized access. Websites Platform management is restricted to Platform Administrators.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:sites,code',
            'default_theme' => 'required|string|max:50',
            'default_locale' => 'required|string|max:10',
            'domain' => 'required|string|max:255|unique:site_domains,domain',
            'is_active' => 'nullable|boolean',
        ]);

        $site = Site::create([
            'name' => $validated['name'],
            'code' => strtolower(trim($validated['code'])),
            'default_theme' => $validated['default_theme'],
            'default_locale' => $validated['default_locale'],
            'is_active' => $request->boolean('is_active', true),
        ]);

        SiteDomain::create([
            'site_id' => $site->id,
            'domain' => strtolower(trim($validated['domain'])),
            'is_primary' => true,
            'is_secure' => true,
        ]);

        SiteContext::flushSiteCache();

        return redirect()->route('admin.sites.index')->with('success', "Website '{$site->name}' created successfully!");
    }

    public function edit(Site $site)
    {
        $this->assertSiteBelongsToApp($site);

        $site->load(['domains', 'settings', 'examOverlays']);
        $allVendors = Vendor::where('is_active', true)->orderBy('name')->get();
        $allExams = Exam::where('is_active', true)->with('vendor')->orderBy('exam_code')->get();

        return view('admin.sites.edit', compact('site', 'allVendors', 'allExams'));
    }

    public function update(Request $request, Site $site)
    {
        $this->assertSiteBelongsToApp($site);
        $tab = $request->input('active_tab', 'general');

        if ($tab === 'general') {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'code' => 'required|string|max:50|unique:sites,code,' . $site->id,
                'default_locale' => 'required|string|max:10',
                'is_active' => 'nullable|boolean',
                'contact_email' => 'nullable|email|max:255',
                'contact_phone' => 'nullable|string|max:50',
                'contact_address' => 'nullable|string|max:500',
            ]);

            $site->update([
                'name' => $validated['name'],
                'code' => strtolower(trim($validated['code'])),
                'default_locale' => $validated['default_locale'],
                'is_active' => $request->boolean('is_active', true),
                'contact_email' => $validated['contact_email'] ?? null,
                'contact_phone' => $validated['contact_phone'] ?? null,
                'contact_address' => $validated['contact_address'] ?? null,
            ]);
        } elseif ($tab === 'branding') {
            $validated = $request->validate([
                'logo' => 'nullable|string|max:255',
                'favicon' => 'nullable|string|max:255',
                'primary_color' => 'required|string|max:20',
                'secondary_color' => 'required|string|max:20',
            ]);

            $site->update($validated);
        } elseif ($tab === 'theme') {
            $validated = $request->validate([
                'default_theme' => 'required|string|max:50',
            ]);

            $site->update($validated);
        } elseif ($tab === 'seo') {
            $validated = $request->validate([
                'default_seo_title' => 'nullable|string|max:255',
                'default_meta_description' => 'nullable|string|max:500',
                'default_h1' => 'nullable|string|max:255',
                'primary_keyword_strategy' => 'nullable|string|max:255',
                'secondary_keyword_strategy' => 'nullable|string',
                'robots_directive' => 'required|string|max:50',
                'canonical_strategy' => 'required|string|max:50',
                'og_image' => 'nullable|string|max:255',
                'twitter_handle' => 'nullable|string|max:50',
            ]);

            $site->update($validated);
        } elseif ($tab === 'analytics') {
            $validated = $request->validate([
                'google_search_console_code' => 'nullable|string|max:255',
                'google_analytics_id' => 'nullable|string|max:50',
                'custom_head_scripts' => 'nullable|string',
                'custom_footer_scripts' => 'nullable|string',
            ]);

            $site->update($validated);
        } elseif ($tab === 'vendors') {
            $selectedVendors = $request->input('vendor_ids', []);
            $site->vendors()->sync($selectedVendors);
        }

        SiteContext::flushSiteCache();

        return redirect()->route('admin.sites.edit', ['site' => $site->id, 'tab' => $tab])
            ->with('success', "Website settings updated successfully!");
    }

    /**
     * Add domain alias to website
     */
    public function addDomain(Request $request, Site $site)
    {
        $this->assertSiteBelongsToApp($site);
        $validated = $request->validate([
            'domain' => 'required|string|max:255|unique:site_domains,domain',
            'is_primary' => 'nullable|boolean',
        ]);

        $isPrimary = $request->boolean('is_primary', false);
        if ($isPrimary) {
            $site->domains()->update(['is_primary' => false]);
        }

        SiteDomain::create([
            'site_id' => $site->id,
            'domain' => strtolower(trim($validated['domain'])),
            'is_primary' => $isPrimary,
            'is_secure' => true,
        ]);

        SiteContext::flushSiteCache();

        return redirect()->route('admin.sites.edit', ['site' => $site->id, 'tab' => 'domain'])
            ->with('success', "Domain '{$validated['domain']}' connected successfully!");
    }

    /**
     * Remove domain alias
     */
    public function removeDomain(Site $site, SiteDomain $domain)
    {
        $this->assertSiteBelongsToApp($site);
        if ($domain->site_id !== $site->id) {
            abort(403, "Domain does not belong to this website.");
        }

        if ($domain->is_primary && $site->domains()->count() > 1) {
            return back()->with('error', 'Cannot delete primary domain. Set another domain as primary first.');
        }

        $domain->delete();
        SiteContext::flushSiteCache();

        return redirect()->route('admin.sites.edit', ['site' => $site->id, 'tab' => 'domain'])
            ->with('success', 'Domain removed.');
    }

    /**
     * Dedicated Manage Exams action page for a site
     */
    public function manageExams(Site $site, Request $request)
    {
        $this->assertSiteBelongsToApp($site);
        $search = trim((string)$request->query('q', ''));
        $vendorFilter = $request->query('vendor_id');
        $statusFilter = $request->query('status'); // all, assigned, unassigned

        // Assigned overlays
        $overlayQuery = $site->examOverlays()->with(['exam.vendor']);

        if (!empty($search)) {
            $overlayQuery->whereHas('exam', function($q) use ($search) {
                $q->whereLike('exam_code', "%{$search}%")
                  ->orWhereLike('exam_name', "%{$search}%");
            });
        }

        if (!empty($vendorFilter)) {
            $overlayQuery->whereHas('exam', function($q) use ($vendorFilter) {
                $q->where('vendor_id', $vendorFilter);
            });
        }

        $assignedOverlays = $overlayQuery->paginate(20)->withQueryString();
        $assignedExamIds = $site->examOverlays()->pluck('exam_id')->toArray();

        // Candidates for assignment
        $allExams = Exam::where('is_active', true)
            ->whereNotIn('id', $assignedExamIds)
            ->with('vendor')
            ->orderBy('exam_code')
            ->get();

        $vendors = Vendor::where('is_active', true)->orderBy('name')->get();

        return view('admin.sites.manage_exams', compact('site', 'assignedOverlays', 'allExams', 'vendors', 'search', 'vendorFilter'));
    }

    /**
     * Assign global exam to site
     */
    public function attachExam(Request $request, Site $site)
    {
        $this->assertSiteBelongsToApp($site);
        $validated = $request->validate([
            'exam_id' => 'required|exists:exams,id',
        ]);

        SiteExamOverlay::firstOrCreate(
            ['site_id' => $site->id, 'exam_id' => $validated['exam_id']],
            [
                'is_active' => true,
                'is_indexed' => true,
            ]
        );

        SiteContext::flushSiteCache();

        return back()->with('success', 'Exam successfully added to website.');
    }

    /**
     * Detach / remove exam from site
     */
    public function detachExam(Site $site, Exam $exam)
    {
        $this->assertSiteBelongsToApp($site);
        $site->examOverlays()->where('exam_id', $exam->id)->delete();
        SiteContext::flushSiteCache();

        return back()->with('success', "Exam {$exam->exam_code} removed from {$site->name}.");
    }

    /**
     * Bulk assign or remove exams for a site
     */
    public function bulkExamAction(Request $request, Site $site)
    {
        $this->assertSiteBelongsToApp($site);
        $action = $request->input('action');
        $examIds = $request->input('exam_ids', []);

        if (empty($examIds)) {
            return back()->with('error', 'No exams selected.');
        }

        if ($action === 'assign') {
            foreach ($examIds as $id) {
                SiteExamOverlay::firstOrCreate(
                    ['site_id' => $site->id, 'exam_id' => $id],
                    ['is_active' => true, 'is_indexed' => true]
                );
            }
            SiteContext::flushSiteCache();
            return back()->with('success', count($examIds) . " exams assigned to {$site->name}.");
        } elseif ($action === 'remove') {
            $site->examOverlays()->whereIn('exam_id', $examIds)->delete();
            SiteContext::flushSiteCache();
            return back()->with('success', count($examIds) . " exams removed from {$site->name}.");
        } elseif ($action === 'activate') {
            $site->examOverlays()->whereIn('exam_id', $examIds)->update(['is_active' => true]);
            SiteContext::flushSiteCache();
            return back()->with('success', "Selected exams activated.");
        } elseif ($action === 'deactivate') {
            $site->examOverlays()->whereIn('exam_id', $examIds)->update(['is_active' => false]);
            SiteContext::flushSiteCache();
            return back()->with('success', "Selected exams set to draft/inactive.");
        }

        return back();
    }

    public function destroy(Site $site)
    {
        $currentAppSiteId = (int)config('site.id', 1);
        if ($currentAppSiteId !== 1) {
            return redirect()->route('admin.sites.edit', $currentAppSiteId)->with('error', 'Site deletion is restricted to Central Core administrators.');
        }

        if ($site->id === 1) {
            return back()->with('error', 'Cannot delete the primary root website.');
        }

        $site->delete();
        SiteContext::flushSiteCache();

        return redirect()->route('admin.sites.index')->with('success', 'Website deleted successfully.');
    }

    /**
     * Show a single site (resource requirement — redirect to edit)
     */
    public function show(Site $site)
    {
        $this->assertSiteBelongsToApp($site);
        return redirect()->route('admin.sites.edit', ['site' => $site->id, 'tab' => 'general']);
    }

    /**
     * Save / update an exam overlay (SEO + content overrides) for this site
     */
    public function saveExamOverlay(Request $request, Site $site)
    {
        $this->assertSiteBelongsToApp($site);
        $validated = $request->validate([
            'exam_id'               => 'required|exists:exams,id',
            'custom_header_title'   => 'nullable|string|max:255',
            'meta_title'            => 'nullable|string|max:255',
            'meta_description'      => 'nullable|string|max:500',
            'custom_article_content'=> 'nullable|string',
            'custom_faqs'           => 'nullable',
            'custom_price_bundle'   => 'nullable|numeric|min:0',
            'is_active'             => 'nullable|boolean',
        ]);

        $overlay = SiteExamOverlay::firstOrNew([
            'site_id' => $site->id,
            'exam_id' => $validated['exam_id'],
        ]);

        $overlay->custom_header_title    = $validated['custom_header_title']    ?? $overlay->custom_header_title;
        $overlay->meta_title             = $validated['meta_title']             ?? $overlay->meta_title;
        $overlay->meta_description       = $validated['meta_description']       ?? $overlay->meta_description;
        $overlay->custom_article_content = $validated['custom_article_content'] ?? $overlay->custom_article_content;
        $overlay->custom_faqs            = $validated['custom_faqs']            ?? $overlay->custom_faqs;
        $overlay->custom_price_bundle    = $validated['custom_price_bundle']    ?? $overlay->custom_price_bundle;
        $overlay->is_active              = $request->boolean('is_active', $overlay->exists ? $overlay->is_active : true);
        $overlay->is_indexed             = true;
        $overlay->save();

        SiteContext::flushSiteCache();

        return redirect()
            ->route('admin.sites.edit', ['site' => $site->id, 'tab' => 'exams'])
            ->with('success', 'Exam overlay saved successfully.');
    }

    /**
     * Dedicated Exam Overlay Editor page for a specific exam on this site.
     */
    public function editExamOverlay(Site $site, Exam $exam)
    {
        $this->assertSiteBelongsToApp($site);

        $overlay = SiteExamOverlay::firstOrNew([
            'site_id' => $site->id,
            'exam_id' => $exam->id,
        ]);

        $exam->load('vendor');

        return view('admin.sites.edit_overlay', compact('site', 'exam', 'overlay'));
    }

    /**
     * Persist dedicated exam overlay updates with full TipTap content and structured FAQs.
     */
    public function updateExamOverlay(Request $request, Site $site, Exam $exam)
    {
        $this->assertSiteBelongsToApp($site);

        $validated = $request->validate([
            'custom_h1'              => 'nullable|string|max:255',
            'custom_header_title'    => 'nullable|string|max:255',
            'meta_title'             => 'nullable|string|max:255',
            'meta_description'       => 'nullable|string|max:500',
            'meta_keywords'          => 'nullable|string|max:500',
            'custom_article_content' => 'nullable|string',
            'custom_price_bundle'    => 'nullable|numeric|min:0',
            'custom_price_engine'    => 'nullable|numeric|min:0',
            'custom_price_pdf'       => 'nullable|numeric|min:0',
            'is_active'              => 'nullable|boolean',
            'is_indexed'             => 'nullable|boolean',
            'faqs'                   => 'nullable|array',
            'faqs.*.question'        => 'nullable|string|max:500',
            'faqs.*.answer'          => 'nullable|string|max:2000',
        ]);

        $overlay = SiteExamOverlay::firstOrNew([
            'site_id' => $site->id,
            'exam_id' => $exam->id,
        ]);

        // Process and clean FAQs array
        $cleanedFaqs = [];
        if (!empty($validated['faqs']) && is_array($validated['faqs'])) {
            foreach ($validated['faqs'] as $faq) {
                $q = trim((string)($faq['question'] ?? ''));
                $a = trim((string)($faq['answer'] ?? ''));
                if ($q !== '' && $a !== '') {
                    $cleanedFaqs[] = [
                        'question' => $q,
                        'answer'   => $a,
                    ];
                }
            }
        }

        // Sanitize TipTap rich text
        $sanitizedArticle = null;
        if (!empty($validated['custom_article_content'])) {
            $sanitizedArticle = HtmlSanitizerService::sanitize($validated['custom_article_content']);
        }

        $overlay->custom_h1              = !empty(trim($validated['custom_h1'] ?? '')) ? trim($validated['custom_h1']) : null;
        $overlay->custom_header_title    = !empty(trim($validated['custom_header_title'] ?? '')) ? trim($validated['custom_header_title']) : null;
        $overlay->meta_title             = !empty(trim($validated['meta_title'] ?? '')) ? trim($validated['meta_title']) : null;
        $overlay->meta_description       = !empty(trim($validated['meta_description'] ?? '')) ? trim($validated['meta_description']) : null;
        $overlay->meta_keywords          = !empty(trim($validated['meta_keywords'] ?? '')) ? trim($validated['meta_keywords']) : null;
        $overlay->custom_article_content = $sanitizedArticle;
        $overlay->custom_faqs            = !empty($cleanedFaqs) ? $cleanedFaqs : null;
        $overlay->custom_price_bundle    = $request->filled('custom_price_bundle') ? (float)$validated['custom_price_bundle'] : null;
        $overlay->custom_price_engine    = $request->filled('custom_price_engine') ? (float)$validated['custom_price_engine'] : null;
        $overlay->custom_price_pdf       = $request->filled('custom_price_pdf') ? (float)$validated['custom_price_pdf'] : null;
        $overlay->is_active              = $request->boolean('is_active');
        $overlay->is_indexed             = $request->boolean('is_indexed');

        // CRITICAL ARCHITECTURE RULE: Only overlay is modified. $exam master record is NEVER touched.
        $overlay->save();

        SiteContext::flushSiteCache();

        return redirect()
            ->route('admin.sites.exams.overlay.edit', ['site' => $site->id, 'exam' => $exam->id])
            ->with('success', "Overlay settings for {$exam->exam_code} updated successfully on {$site->name}.");
    }
}

