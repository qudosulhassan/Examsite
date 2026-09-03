<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class ExamAdminController extends Controller
{
    public function index(Request $request)
    {
        $query = Exam::with('vendor');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('exam_code', 'like', "%{$search}%")
                  ->orWhere('exam_name', 'like', "%{$search}%");
            });
        }

        $exams = $query->orderBy('exam_code')->paginate(10)->withQueryString();
        
        return view('admin.exams.index', compact('exams'));
    }

    public function searchSuggestions(Request $request)
    {
        $query = Exam::query();
        if ($request->filled('query')) {
            $search = $request->input('query');
            $query->where(function($q) use ($search) {
                $q->where('exam_code', 'like', "%{$search}%")
                  ->orWhere('exam_name', 'like', "%{$search}%");
            });
        }
        
        $exams = $query->orderBy('exam_code')->take(10)->get(['id', 'exam_code', 'exam_name']);
        
        return response()->json($exams);
    }

    public function create()
    {
        $vendors = Vendor::where('is_active', true)->orderBy('name')->get();
        $certifications = \App\Models\Certification::orderBy('name')->get();
        return view('admin.exams.create', compact('vendors', 'certifications'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'vendor_id' => 'required|exists:vendors,id',
            'exam_code' => 'required|string|unique:exams,exam_code',
            'exam_name' => 'required|string|max:255',
            'price_pdf' => 'required|numeric|min:0',
            'price_engine' => 'required|numeric|min:0',
            'update_price_3_months' => 'nullable|numeric|min:0',
            'update_price_6_months' => 'nullable|numeric|min:0',
            'update_price_12_months' => 'nullable|numeric|min:0',
            'passing_score' => 'required|integer|min:50|max:100',
            'difficulty' => 'required|in:Associate,Professional,Expert',
            'exam_type' => 'required|in:MultipleChoice,MultiSelect,LabBased',
            'topics' => 'nullable|string', // comma-separated strings
            'demo_pdf' => 'nullable|file|mimes:pdf|max:20480',
            'full_pdf' => 'nullable|file|mimes:pdf|max:51200',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'meta_keywords' => 'nullable|string|max:255',
        ]);

        $topicsArray = $request->topics ? array_map('trim', explode(',', $request->topics)) : [];

        $demoFilename = null;
        if ($request->hasFile('demo_pdf')) {
            $demoFile = $request->file('demo_pdf');
            $demoFilename = Str::slug($request->exam_code) . '-demo.pdf';
            try {
                Storage::disk('r2')->putFileAs('demos', $demoFile, $demoFilename);
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('R2 upload failed for demo PDF: ' . $e->getMessage());
            }
            Storage::disk('public')->putFileAs('demos', $demoFile, $demoFilename);
        }

        $fullFilename = null;
        if ($request->hasFile('full_pdf')) {
            $fullFile = $request->file('full_pdf');
            $fullFilename = Str::slug($request->exam_code) . '-full.pdf';
            try {
                Storage::disk('r2')->putFileAs('full', $fullFile, $fullFilename);
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('R2 upload failed for full PDF: ' . $e->getMessage());
            }
            Storage::disk('public')->putFileAs('full', $fullFile, $fullFilename);
        }

        $exam = Exam::create([
            'vendor_id' => $request->vendor_id,
            'exam_code' => $request->exam_code,
            'exam_name' => $request->exam_name,
            'slug' => Str::slug($request->exam_code),
            'price_pdf' => $request->price_pdf,
            'price_engine' => $request->price_engine,
            'update_price_3_months' => $request->update_price_3_months ?? 0,
            'update_price_6_months' => $request->update_price_6_months ?? 10,
            'update_price_12_months' => $request->update_price_12_months ?? 20,
            'passing_score' => $request->passing_score,
            'difficulty' => $request->difficulty,
            'exam_type' => $request->exam_type,
            'topics' => $topicsArray,
            'description' => $request->description,
            'is_active' => $request->has('is_active'),
            'demo_pdf_filename' => $demoFilename,
            'full_pdf_filename' => $fullFilename,
            'meta_title' => $request->meta_title,
            'meta_description' => $request->meta_description,
            'meta_keywords' => $request->meta_keywords,
            'last_updated_at' => now(),
        ]);

        if ($request->has('certifications')) {
            $exam->certifications()->sync($request->certifications);
        }

        return redirect()->route('admin.exams.index')->with('success', 'Exam created successfully.');
    }

    public function edit(int $id)
    {
        $exam = Exam::with('certifications')->findOrFail($id);
        $vendors = Vendor::where('is_active', true)->orderBy('name')->get();
        $certifications = \App\Models\Certification::orderBy('name')->get();
        return view('admin.exams.edit', compact('exam', 'vendors', 'certifications'));
    }

    public function update(Request $request, int $id)
    {
        $exam = Exam::findOrFail($id);

        $request->validate([
            'vendor_id' => 'required|exists:vendors,id',
            'exam_code' => 'required|string|unique:exams,exam_code,' . $exam->id,
            'exam_name' => 'required|string|max:255',
            'price_pdf' => 'required|numeric|min:0',
            'price_engine' => 'required|numeric|min:0',
            'update_price_3_months' => 'nullable|numeric|min:0',
            'update_price_6_months' => 'nullable|numeric|min:0',
            'update_price_12_months' => 'nullable|numeric|min:0',
            'passing_score' => 'required|integer|min:50|max:100',
            'difficulty' => 'required|in:Associate,Professional,Expert',
            'exam_type' => 'required|in:MultipleChoice,MultiSelect,LabBased',
            'topics' => 'nullable|string',
            'demo_pdf' => 'nullable|file|mimes:pdf|max:20480',
            'full_pdf' => 'nullable|file|mimes:pdf|max:51200',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'meta_keywords' => 'nullable|string|max:255',
        ]);

        $topicsArray = $request->topics ? array_map('trim', explode(',', $request->topics)) : [];

        $updateData = [
            'vendor_id' => $request->vendor_id,
            'exam_code' => $request->exam_code,
            'exam_name' => $request->exam_name,
            'slug' => Str::slug($request->exam_code),
            'price_pdf' => $request->price_pdf,
            'price_engine' => $request->price_engine,
            'update_price_3_months' => $request->update_price_3_months ?? 0,
            'update_price_6_months' => $request->update_price_6_months ?? 10,
            'update_price_12_months' => $request->update_price_12_months ?? 20,
            'passing_score' => $request->passing_score,
            'difficulty' => $request->difficulty,
            'exam_type' => $request->exam_type,
            'topics' => $topicsArray,
            'description' => $request->description,
            'is_active' => $request->has('is_active'),
            'meta_title' => $request->meta_title,
            'meta_description' => $request->meta_description,
            'meta_keywords' => $request->meta_keywords,
            'last_updated_at' => now(),
        ];

        if ($request->hasFile('demo_pdf')) {
            $demoFile = $request->file('demo_pdf');
            $demoFilename = Str::slug($request->exam_code) . '-demo.pdf';
            try {
                Storage::disk('r2')->putFileAs('demos', $demoFile, $demoFilename);
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('R2 upload failed for demo PDF: ' . $e->getMessage());
            }
            Storage::disk('public')->putFileAs('demos', $demoFile, $demoFilename);
            $updateData['demo_pdf_filename'] = $demoFilename;
        }

        if ($request->hasFile('full_pdf')) {
            $fullFile = $request->file('full_pdf');
            $fullFilename = Str::slug($request->exam_code) . '-full.pdf';
            try {
                Storage::disk('r2')->putFileAs('full', $fullFile, $fullFilename);
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('R2 upload failed for full PDF: ' . $e->getMessage());
            }
            Storage::disk('public')->putFileAs('full', $fullFile, $fullFilename);
            $updateData['full_pdf_filename'] = $fullFilename;
        }

        $exam->update($updateData);

        if ($request->has('certifications')) {
            $exam->certifications()->sync($request->certifications);
        } else {
            $exam->certifications()->sync([]);
        }

        // Retrieve all users who have access to this exam
        $purchasedUserIds = \App\Models\UserExam::where('exam_id', $exam->id)
            ->pluck('user_id')
            ->unique();
        $purchasedUsers = \App\Models\User::whereIn('id', $purchasedUserIds)->get();

        foreach ($purchasedUsers as $user) {
            try {
                \Illuminate\Support\Facades\Mail::to($user->email)->queue(new \App\Mail\ExamUpdatedMail($user, $exam));
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Failed to dispatch exam updated email to ' . $user->email . ': ' . $e->getMessage());
            }
        }

        return redirect()->route('admin.exams.index')->with('success', 'Exam updated successfully.');
    }

    public function destroy(int $id)
    {
        $exam = Exam::findOrFail($id);
        $exam->delete();
        return redirect()->route('admin.exams.index')->with('success', 'Exam deleted successfully.');
    }
}
