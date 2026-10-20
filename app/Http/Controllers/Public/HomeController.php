<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Vendor;
use App\Models\Exam;

class HomeController extends Controller
{
    /**
     * Display the website home page.
     */
    public function index()
    {
        // Get active vendors with their active exams count
        $vendors = Vendor::where('is_active', true)
            ->withCount('exams')
            ->orderBy('sort_order')
            ->get();

        // Get 8 most recently updated active exams
        $latestExams = Exam::where('is_active', true)
            ->with('vendor')
            ->orderBy('last_updated_at', 'desc')
            ->limit(8)
            ->get();

        // Single-vendor sites (SITE_VENDORS) build their homepage from the vendor's whole catalog,
        // grouped into tracks. Other sites skip this, so their homepage query count is unchanged.
        $siteVendor = null;
        $siteTracks = [];
        $siteQuestionCount = 0;
        $siteBundle = null;

        if (count((array) config('site.vendors', [])) === 1 && $vendors->count() === 1) {
            $siteVendor = $vendors->first();
            $siteExams = Exam::where('is_active', true)->with(['vendor', 'overlays'])->orderBy('exam_code')->get();
            $siteTracks = \App\Support\ExamTracks::group($siteExams, $siteVendor->slug);
            $siteQuestionCount = \App\Models\Question::where('is_active', true)->whereIn('exam_id', $siteExams->pluck('id'))->count();
            $siteBundle = \App\Models\Package::where('is_active', true)->where('type', 'bundle')->orderBy('sort_order')->first();
            $latestExams = $siteExams->sortByDesc('last_updated_at')->take(8)->values();
        }

        return view('pages.home', compact('vendors', 'latestExams', 'siteVendor', 'siteTracks', 'siteQuestionCount', 'siteBundle'));
    }

    /**
     * Display the public Test Engine marketing page.
     */
    public function testEngine(\Illuminate\Http\Request $request)
    {
        $totalExams = Exam::where('is_active', true)->count();
        $totalQuestions = \App\Models\Question::where('is_active', true)->count();
        
        $searchQuery = trim($request->get('q', ''));
        $vendorFilter = trim($request->get('vendor', ''));

        $query = Exam::where('is_active', true)
            ->whereHas('questions')
            ->with('vendor');

        if (!empty($searchQuery)) {
            $query->where(function ($q) use ($searchQuery) {
                $q->whereLike('exam_code', "%{$searchQuery}%")
                  ->orWhereLike('exam_name', "%{$searchQuery}%")
                  ->orWhereHas('vendor', function($v) use ($searchQuery) {
                      $v->whereLike('name', "%{$searchQuery}%");
                  });
            });
        }

        if (!empty($vendorFilter)) {
            $query->whereHas('vendor', function($v) use ($vendorFilter) {
                $v->where('slug', $vendorFilter);
            });
        }

        $compatibleExams = $query->orderBy('exam_code')
            ->paginate(12)
            ->appends($request->all());

        // Get top vendors with compatible exams for filter pills
        $vendors = Vendor::where('is_active', true)
            ->whereHas('exams', function($e) {
                $e->whereHas('questions');
            })
            ->limit(8)
            ->get();

        if ($request->ajax() || $request->wantsJson() || $request->has('ajax')) {
            $gridHtml = view('pages.partials.test-engine-grid', compact('compatibleExams', 'searchQuery', 'vendorFilter'))->render();
            return response()->json([
                'success' => true,
                'html' => $gridHtml,
                'total' => $compatibleExams->total(),
                'has_filters' => !empty($searchQuery) || !empty($vendorFilter),
                'search_query' => $searchQuery,
                'vendor_filter' => $vendorFilter,
            ]);
        }

        return view('pages.test-engine', compact(
            'totalExams',
            'totalQuestions',
            'compatibleExams',
            'searchQuery',
            'vendorFilter',
            'vendors'
        ));
    }
}
