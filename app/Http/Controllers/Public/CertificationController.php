<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Certification;
use App\Models\Vendor;

class CertificationController extends Controller
{
    public function index()
    {
        // Fetch vendors that have at least one active certification, eager loading those certifications
        $vendors = Vendor::where('is_active', true)
            ->whereHas('certifications', function ($query) {
                $query->where('is_active', true);
            })
            ->with(['certifications' => function ($query) {
                $query->where('is_active', true)->orderBy('sort_order');
            }])
            ->orderBy('name')
            ->get();

        return view('pages.certifications.index', compact('vendors'));
    }

    public function show($slug)
    {
        $certification = Certification::with(['vendor', 'exams' => function($q) {
            $q->where('is_active', true)->orderBy('exam_code');
        }])
        ->where('is_active', true)
        ->where(function($q) use ($slug) {
            $q->where('slug', $slug)
              ->orWhere('slug', strtolower($slug))
              ->orWhere('name', 'like', "%{$slug}%");
        })
        ->first();

        if (!$certification) {
            abort(404);
        }

        if ($slug !== $certification->slug) {
            return redirect()->route('certifications.show', $certification->slug, 301);
        }

        return view('pages.certifications.show', compact('certification'));
    }
}
