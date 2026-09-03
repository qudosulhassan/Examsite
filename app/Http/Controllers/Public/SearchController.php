<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\Vendor;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    /**
     * Handle AJAX live search query (returns JSON).
     */
    public function liveSearch(Request $request)
    {
        $query = $request->get('q', '');
        $context = $request->get('context', '');

        if (strlen($query) < 2) {
            return response()->json(['exams' => [], 'vendors' => []]);
        }

        // Search exams by code or name
        $exams = Exam::where('is_active', true)
            ->where(function ($q) use ($query) {
                $q->where('exam_code', 'like', "%{$query}%")
                  ->orWhere('exam_name', 'like', "%{$query}%");
            })
            ->with('vendor')
            ->limit(5)
            ->get(['id', 'exam_code', 'exam_name', 'slug', 'vendor_id']);

        // Search vendors by name
        $vendors = Vendor::where('is_active', true)
            ->where('name', 'like', "%{$query}%")
            ->limit(3)
            ->get(['id', 'name', 'slug', 'logo_path']);

        return response()->json([
            'exams' => $exams->map(function($exam) use ($context) {
                $demoUrl = route('public.demo-test-engine.lobby', $exam->slug);
                $productUrl = $exam->url;
                return [
                    'code' => $exam->exam_code,
                    'name' => $exam->exam_name,
                    'url' => ($context === 'test-engine' || $context === 'demo') ? $demoUrl : $productUrl,
                    'demo_url' => $demoUrl,
                    'product_url' => $productUrl,
                    'vendor' => $exam->vendor ? $exam->vendor->name : '',
                ];
            }),
            'vendors' => $vendors->map(function($vendor) {
                return [
                    'name' => $vendor->name,
                    'url' => url('/vendors/' . $vendor->slug),
                ];
            })
        ]);
    }

    /**
     * Display full search results view with filters.
     */
    public function search(Request $request)
    {
        $query = $request->get('q', '');

        $examsQuery = Exam::where('is_active', true)
            ->with('vendor');

        if (!empty($query)) {
            $examsQuery->where(function ($q) use ($query) {
                $q->where('exam_code', 'like', "%{$query}%")
                  ->orWhere('exam_name', 'like', "%{$query}%")
                  ->orWhereHas('vendor', function($v) use ($query) {
                      $v->where('name', 'like', "%{$query}%");
                  });
            });
        }

        // Apply filters if present
        if ($request->has('difficulty') && !empty($request->difficulty)) {
            $examsQuery->where('difficulty', $request->difficulty);
        }

        if ($request->has('type') && !empty($request->type)) {
            $examsQuery->where('exam_type', $request->type);
        }

        if ($request->has('price_max') && is_numeric($request->price_max)) {
            $examsQuery->where('price_pdf', '<=', $request->price_max);
        }

        $exams = $examsQuery->paginate(12)->appends($request->all());

        return view('pages.search', compact('exams', 'query'));
    }
}
