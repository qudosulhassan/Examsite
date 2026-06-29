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
            ->orderBy('sort_order')
            ->get();

        // Get 8 most recently updated active exams
        $latestExams = Exam::where('is_active', true)
            ->with('vendor')
            ->orderBy('last_updated_at', 'desc')
            ->limit(8)
            ->get();

        return view('pages.home', compact('vendors', 'latestExams'));
    }

    /**
     * Display the public Test Engine marketing page.
     */
    public function testEngine()
    {
        $totalExams = Exam::where('is_active', true)->count();
        $totalQuestions = \App\Models\Question::where('is_active', true)->count();
        
        $compatibleExams = Exam::where('is_active', true)
            ->whereHas('questions')
            ->with('vendor')
            ->orderBy('exam_code')
            ->limit(6)
            ->get();

        return view('pages.test-engine', compact('totalExams', 'totalQuestions', 'compatibleExams'));
    }
}
