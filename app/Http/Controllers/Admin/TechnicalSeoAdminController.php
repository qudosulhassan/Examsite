<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\Redirect;
use App\Models\SeoNotFoundLog;
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
        $activeTab = $request->get('tab', 'overview');
        $settings = Setting::all()->pluck('value', 'key')->toArray();

        // Ensure defaults
        $settings['site_name'] = $settings['site_name'] ?? config('app.name', 'Exam Topics Base');
        $settings['site_url'] = $settings['site_url'] ?? config('app.url', 'http://127.0.0.1:8000');
        $settings['seo_robots_index'] = $settings['seo_robots_index'] ?? 'index';
        $settings['seo_robots_follow'] = $settings['seo_robots_follow'] ?? 'follow';
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
            'schemaPreviews'
        ));
    }

    /**
     * Save SEO settings form.
     */
    public function updateSettings(Request $request)
    {
        $tab = $request->get('active_tab', 'overview');
        $inputs = $request->except(['_token', 'active_tab']);

        foreach ($inputs as $key => $value) {
            Setting::set($key, is_array($value) ? json_encode($value) : (string)$value);
        }

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
