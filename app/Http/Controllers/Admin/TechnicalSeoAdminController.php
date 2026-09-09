<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\Redirect;
use App\Models\SeoNotFoundLog;
use App\Models\Exam;
use App\Models\Vendor;
use App\Services\TechnicalSeoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class TechnicalSeoAdminController extends Controller
{
    protected TechnicalSeoService $seoService;

    public function __construct(TechnicalSeoService $seoService)
    {
        $this->seoService = $seoService;
    }

    /**
     * Master Technical SEO Center View.
     */
    public function index(Request $request)
    {
        if ($request->boolean('refresh')) {
            \Illuminate\Support\Facades\Artisan::call('view:clear');
            session()->flash('success', 'Technical SEO audit successfully re-run against live database.');
        }

        $activeTab = $request->get('tab', $request->get('section', 'overview'));
        $settings = Setting::all()->pluck('value', 'key')->toArray();

        // Ensure defaults
        $settings['site_name'] = $settings['site_name'] ?? config('app.name', 'Exam Topics Base');
        $settings['site_url'] = $settings['site_url'] ?? config('app.url', 'http://127.0.0.1:8000');
        $settings['seo_robots_index'] = $settings['seo_robots_index'] ?? 'index';
        $settings['seo_robots_follow'] = $settings['seo_robots_follow'] ?? 'follow';
        $settings['seo_robots_noarchive'] = $settings['seo_robots_noarchive'] ?? '0';
        $settings['seo_robots_nosnippet'] = $settings['seo_robots_nosnippet'] ?? '0';
        $settings['seo_robots_max_image_preview'] = $settings['seo_robots_max_image_preview'] ?? '1';
        $settings['seo_canonical_force_https'] = $settings['seo_canonical_force_https'] ?? '1';
        $settings['seo_schema_master_enabled'] = $settings['seo_schema_master_enabled'] ?? '1';

        // Health Audit
        $healthAudit = $this->seoService->runHealthAudit();

        // Sitemap Data
        $sitemapData = $this->seoService->getSitemapData();

        // Robots.txt content
        $robotsContent = $this->seoService->getRobotsTxtContent();

        // Redirects with search/filter (safe if table or column missing before migration)
        $redirects = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 15);
        if (\Illuminate\Support\Facades\Schema::hasTable('redirects')) {
            try {
                $redirectsQuery = Redirect::query();
                if (\Illuminate\Support\Facades\Schema::hasColumn('redirects', 'hits_count')) {
                    $redirectsQuery->orderBy('hits_count', 'desc');
                } else {
                    $redirectsQuery->orderBy('id', 'desc');
                }

                $redirectSearch = $request->get('redirect_search');
                if ($redirectSearch) {
                    $redirectsQuery->where(function($q) use ($redirectSearch) {
                        $q->where('old_url', 'like', "%{$redirectSearch}%")
                          ->orWhere('new_url', 'like', "%{$redirectSearch}%");
                    });
                }
                $redirects = $redirectsQuery->paginate(15)->withQueryString();
            } catch (\Throwable $th) {
                \Illuminate\Support\Facades\Log::warning('Redirects query failed: ' . $th->getMessage());
            }
        }

        // 404 Logs (safe if table missing before migration)
        $notFoundLogs = collect([]);
        if (\Illuminate\Support\Facades\Schema::hasTable('seo_not_found_logs')) {
            try {
                $notFoundQuery = SeoNotFoundLog::query();
                if (\Illuminate\Support\Facades\Schema::hasColumn('seo_not_found_logs', 'hits_count')) {
                    $notFoundQuery->orderBy('hits_count', 'desc');
                }
                if (\Illuminate\Support\Facades\Schema::hasColumn('seo_not_found_logs', 'last_seen_at')) {
                    $notFoundQuery->orderBy('last_seen_at', 'desc');
                }
                $notFoundLogs = $notFoundQuery->take(50)->get();
            } catch (\Throwable $th) {
                \Illuminate\Support\Facades\Log::warning('SEO 404 logs query failed: ' . $th->getMessage());
            }
        }

        // Internal Linking Data
        $internalLinking = $this->seoService->analyzeInternalLinking();

        // Performance Diagnostics
        $performance = $this->seoService->getPerformanceDiagnostics();

        // Schema Previews
        $schemaPreviews = [
            'organization' => $this->seoService->getPreviewSchema('organization'),
            'website' => $this->seoService->getPreviewSchema('website'),
            'breadcrumbs' => $this->seoService->getPreviewSchema('breadcrumbs'),
            'product' => $this->seoService->getPreviewSchema('product'),
            'course' => $this->seoService->getPreviewSchema('course'),
            'article' => $this->seoService->getPreviewSchema('article'),
        ];

        // Search Engine Verification Live Check (only when accessing the search_engines tab to keep other tabs instant)
        $verificationResults = ($activeTab === 'search_engines') 
            ? $this->seoService->verifySearchEngineTokensLive() 
            : [];

        return view('admin.seo.index', compact(
            'activeTab',
            'settings',
            'healthAudit',
            'sitemapData',
            'robotsContent',
            'redirects',
            'notFoundLogs',
            'internalLinking',
            'performance',
            'schemaPreviews',
            'verificationResults'
        ));
    }

    /**
     * Save SEO settings form.
     */
    public function updateSettings(Request $request)
    {
        $tab = $request->get('active_tab', 'overview');

        if ($tab === 'meta_indexing') {
            $validated = $request->validate([
                'seo_robots_index' => 'required|string|in:index,noindex',
                'seo_robots_follow' => 'required|string|in:follow,nofollow',
                'seo_robots_noarchive' => 'nullable|in:0,1',
                'seo_robots_nosnippet' => 'nullable|in:0,1',
                'seo_robots_max_image_preview' => 'nullable|in:0,1',
                'default_og_title' => 'nullable|string|max:255',
                'default_og_description' => 'nullable|string|max:1000',
                'seo_twitter_card' => 'nullable|string|in:summary,summary_large_image',
                'seo_facebook_app_id' => 'nullable|string|max:100',
            ]);

            // Ensure checkbox booleans are normalized ('1' or '0')
            Setting::set('seo_robots_index', $validated['seo_robots_index']);
            Setting::set('seo_robots_follow', $validated['seo_robots_follow']);
            Setting::set('robots_setting', $validated['seo_robots_index'] . ', ' . $validated['seo_robots_follow']);
            Setting::set('seo_robots_noarchive', $request->input('seo_robots_noarchive') === '1' ? '1' : '0');
            Setting::set('seo_robots_nosnippet', $request->input('seo_robots_nosnippet') === '1' ? '1' : '0');
            Setting::set('seo_robots_max_image_preview', $request->input('seo_robots_max_image_preview') === '1' ? '1' : '0');

            if ($request->has('default_og_title')) {
                Setting::set('default_og_title', (string) $request->input('default_og_title'));
            }
            if ($request->has('default_og_description')) {
                Setting::set('default_og_description', (string) $request->input('default_og_description'));
            }
            if ($request->has('seo_twitter_card')) {
                Setting::set('seo_twitter_card', (string) $request->input('seo_twitter_card'));
            }
            if ($request->has('seo_facebook_app_id')) {
                Setting::set('seo_facebook_app_id', (string) $request->input('seo_facebook_app_id'));
            }

            Setting::clearCache();

            return redirect()->route('admin.seo.index', ['tab' => $tab])
                ->with('success', 'Meta & Indexing directives saved and applied successfully.');
        }

        if ($tab === 'search_engines') {
            $validated = $request->validate([
                'seo_gsc_verification' => 'nullable|string',
                'seo_bing_verification' => 'nullable|string',
                'seo_yandex_verification' => 'nullable|string',
                'seo_pinterest_verification' => 'nullable|string',
            ]);

            // Clean inputs to ensure only raw tokens are stored (even if user pasted full HTML meta tags)
            $cleanGsc = $this->seoService->cleanVerificationToken($validated['seo_gsc_verification'] ?? null);
            $cleanBing = $this->seoService->cleanVerificationToken($validated['seo_bing_verification'] ?? null);
            $cleanYandex = $this->seoService->cleanVerificationToken($validated['seo_yandex_verification'] ?? null);
            $cleanPinterest = $this->seoService->cleanVerificationToken($validated['seo_pinterest_verification'] ?? null);

            Setting::set('seo_gsc_verification', $cleanGsc);
            Setting::set('seo_bing_verification', $cleanBing);
            Setting::set('seo_yandex_verification', $cleanYandex);
            Setting::set('seo_pinterest_verification', $cleanPinterest);

            Setting::clearCache();

            return redirect()->route('admin.seo.index', ['tab' => $tab])
                ->with('success', 'Search engine verification tokens saved and cleaned successfully.');
        }

        $inputs = $request->except(['_token', 'active_tab']);

        foreach ($inputs as $key => $value) {
            Setting::set($key, is_array($value) ? json_encode($value) : (string)$value);
        }

        Setting::clearCache();

        return redirect()->route('admin.seo.index', ['tab' => $tab])
            ->with('success', 'Technical SEO settings saved successfully.');
    }

    /**
     * Regenerate XML Sitemap.
     */
    public function regenerateSitemap()
    {
        $result = $this->seoService->regenerateSitemap();

        return redirect()->route('admin.seo.index', ['tab' => 'sitemap'])
            ->with('success', "XML Sitemap regenerated successfully with {$result['count']} public URLs.");
    }

    /**
     * Save Robots.txt.
     */
    public function saveRobots(Request $request)
    {
        $request->validate([
            'robots_content' => 'required|string',
        ]);

        $result = $this->seoService->saveRobotsTxt($request->input('robots_content'));

        $msg = $result['message'];
        if (!empty($result['warnings'])) {
            $msg .= ' Warnings: ' . implode(' ', $result['warnings']);
        }

        return redirect()->route('admin.seo.index', ['tab' => 'robots'])->with('success', $msg);
    }

    /**
     * Reset Robots.txt to default.
     */
    public function resetRobots()
    {
        $this->seoService->resetRobotsTxt();

        return redirect()->route('admin.seo.index', ['tab' => 'robots'])
            ->with('success', 'Robots.txt has been reset to recommended default rules.');
    }

    /**
     * Create a new Redirect rule.
     */
    public function storeRedirect(Request $request)
    {
        $request->validate([
            'old_url' => 'required|string',
            'new_url' => 'required|string',
            'status_code' => 'required|in:301,302,307,308',
        ]);

        $source = Redirect::normalizeUrl($request->input('old_url'));
        $destination = Redirect::normalizeUrl($request->input('new_url'));

        if (Redirect::wouldCauseLoop($source, $destination)) {
            return back()->with('error', 'Error: This redirect would create a circular redirect loop. Action aborted.');
        }

        if ($error = $this->validateDestinationExam($destination)) {
            return back()->with('error', $error);
        }

        Redirect::updateOrCreate(
            ['old_url' => $source],
            [
                'new_url' => $destination,
                'status_code' => (int)$request->input('status_code', 301),
                'is_active' => true,
            ]
        );

        return redirect()->route('admin.seo.index', ['tab' => 'redirects'])
            ->with('success', "Redirect rule from {$source} to {$destination} saved successfully.");
    }

    /**
     * Toggle redirect status.
     */
    public function toggleRedirect($id)
    {
        $redirect = Redirect::findOrFail($id);
        $redirect->is_active = !$redirect->is_active;
        $redirect->save();

        return redirect()->route('admin.seo.index', ['tab' => 'redirects'])
            ->with('success', 'Redirect rule status updated.');
    }

    /**
     * Delete a Redirect rule.
     */
    public function destroyRedirect($id)
    {
        $redirect = Redirect::findOrFail($id);
        $redirect->delete();

        return redirect()->route('admin.seo.index', ['tab' => 'redirects'])
            ->with('success', 'Redirect rule removed.');
    }

    /**
     * Resolve 404 Log by creating a 301 redirect.
     */
    public function resolveNotFound(Request $request, $id)
    {
        $log = SeoNotFoundLog::findOrFail($id);

        $request->validate([
            'destination_url' => 'required|string',
        ]);

        $source = Redirect::normalizeUrl($log->url);
        $destination = Redirect::normalizeUrl($request->input('destination_url'));

        if (Redirect::wouldCauseLoop($source, $destination)) {
            return back()->with('error', 'Error: Creating this redirect would form a redirect loop.');
        }

        if ($error = $this->validateDestinationExam($destination)) {
            return back()->with('error', $error);
        }

        Redirect::updateOrCreate(
            ['old_url' => $source],
            [
                'new_url' => $destination,
                'status_code' => 301,
                'is_active' => true,
            ]
        );

        $log->update(['is_resolved' => true]);

        return redirect()->route('admin.seo.index', ['tab' => 'redirects'])
            ->with('success', "404 for {$source} resolved with 301 redirect to {$destination}.");
    }

    /**
     * Validate that if a destination is an exam URL, the target exam actually exists.
     */
    protected function validateDestinationExam(string $destination): ?string
    {
        $clean = trim($destination, '/');
        $parts = explode('/', $clean);
        if (count($parts) === 3 && $parts[0] === 'exams') {
            $vSlug = $parts[1];
            $eSlug = $parts[2];
            $exam = Exam::where('is_active', true)
                ->where(function ($q) use ($eSlug) {
                    $q->where('slug', $eSlug)->orWhere('exam_code', $eSlug);
                })
                ->whereHas('vendor', function ($q) use ($vSlug) {
                    $q->where('slug', $vSlug)->where('is_active', true);
                })
                ->first();

            if (!$exam) {
                return "The destination exam '/exams/{$vSlug}/{$eSlug}' does not exist in the database. Redirects cannot point to non-existent exams.";
            }
        }
        return null;
    }

    /**
     * Clear 404 Logs.
     */
    public function clearNotFoundLogs(Request $request)
    {
        $onlyResolved = $request->get('type') === 'resolved';

        if ($onlyResolved) {
            SeoNotFoundLog::where('is_resolved', true)->delete();
            $msg = 'Resolved 404 logs cleared.';
        } else {
            SeoNotFoundLog::truncate();
            $msg = 'All 404 error logs have been cleared.';
        }

        return redirect()->route('admin.seo.index', ['tab' => 'redirects'])->with('success', $msg);
    }
}
