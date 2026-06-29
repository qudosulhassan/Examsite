<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\DemoRequest;
use App\Jobs\SendDemoPdfEmail;
use Illuminate\Http\Request;

class DemoController extends Controller
{
    /**
     * Display the free demo request landing page.
     */
    public function index()
    {
        // Get active exams list for selection dropdown
        $exams = Exam::where('is_active', true)
            ->with('vendor')
            ->orderBy('exam_code')
            ->get();

        return view('pages.free-demo', compact('exams'));
    }

    /**
     * Handle submission of a demo request.
     */
    public function request(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'exam_id' => 'required|exists:exams,id',
        ]);

        $exam = Exam::findOrFail($request->exam_id);

        // Record request in database
        $demoRequest = DemoRequest::create([
            'name' => $request->name,
            'email' => $request->email,
            'exam_id' => $request->exam_id,
            'ip_address' => $request->ip(),
        ]);

        // Dispatch queued email job (non-blocking)
        SendDemoPdfEmail::dispatch($demoRequest);

        return back()->with('status', 'Success! Your free demo PDF download link has been sent to your email (' . $request->email . '). Please check your inbox shortly.');
    }
}
