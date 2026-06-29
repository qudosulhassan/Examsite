<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Vendor;
use App\Models\Exam;
use Illuminate\Http\Request;

class VendorController extends Controller
{
    /**
     * Display a listing of all vendors.
     */
    public function index(Request $request)
    {
        $vendorsQuery = Vendor::where('is_active', true)->orderBy('sort_order');
        


        $vendors = $vendorsQuery->get();

        return view('pages.vendors.index', compact('vendors'));
    }

    /**
     * Display a specific vendor and their exams list.
     */
    public function show(Request $request, string $slug)
    {
        $vendor = Vendor::where('slug', $slug)->where('is_active', true)->firstOrFail();
        
        $examsQuery = Exam::with('vendor')->where('vendor_id', $vendor->id)->where('is_active', true);

        // Filter by level (Associate, Professional, Expert)
        if ($request->has('difficulty') && in_array($request->difficulty, ['Associate', 'Professional', 'Expert'])) {
            $examsQuery->where('difficulty', $request->difficulty);
        }

        // Sort by price or date
        $sortBy = $request->get('sort', 'code');
        if ($sortBy === 'price_low') {
            $examsQuery->orderBy('price_pdf', 'asc');
        } elseif ($sortBy === 'price_high') {
            $examsQuery->orderBy('price_pdf', 'desc');
        } elseif ($sortBy === 'updated') {
            $examsQuery->orderBy('last_updated_at', 'desc');
        } else {
            $examsQuery->orderBy('sort_order')->orderBy('exam_code', 'asc');
        }

        $exams = $examsQuery->get();

        $vendorPackages = \App\Models\Package::where('vendor_id', $vendor->id)
                                             ->where('is_active', true)
                                             ->orderBy('sort_order', 'asc')
                                             ->get();

        return view('pages.vendors.show', compact('vendor', 'exams', 'vendorPackages'));
    }
}
