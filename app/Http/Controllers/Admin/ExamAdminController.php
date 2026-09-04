<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\Vendor;
use App\Models\Certification;
use App\Models\UserExam;
use App\Models\OrderItem;
use App\Services\AuditLogService;
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
        $isConfigured = $request->has('availability_configured');
        $isPdf = $isConfigured ? $request->boolean('is_pdf_available') : ($request->has('is_pdf_available') ? $request->boolean('is_pdf_available') : true);
        $isEngine = $isConfigured ? $request->boolean('is_engine_available') : ($request->has('is_engine_available') ? $request->boolean('is_engine_available') : true);
        $isBundle = $isConfigured ? $request->boolean('is_bundle_available') : ($request->has('is_bundle_available') ? $request->boolean('is_bundle_available') : true);

        if (!$isPdf && !$isEngine && !$isBundle) {
            return back()->withInput()->withErrors([
                'product_availability' => 'At least one purchase option must be enabled.'
            ]);
        }

        $request->validate([
            'vendor_id' => 'required|exists:vendors,id',
            'exam_code' => 'required|string|max:100|unique:exams,exam_code',
            'exam_name' => 'required|string|max:255',
            'header_title' => 'nullable|string|max:255',
            'slug' => 'nullable|string|max:255',
            'is_pdf_available' => 'nullable|boolean',
            'is_engine_available' => 'nullable|boolean',
            'is_bundle_available' => 'nullable|boolean',
            'price_pdf' => ($isPdf && ($isConfigured || $request->has('price_pdf'))) ? 'required|numeric|min:0' : 'nullable|numeric|min:0',
            'price_engine' => ($isEngine && ($isConfigured || $request->has('price_engine'))) ? 'required|numeric|min:0' : 'nullable|numeric|min:0',
            'price_bundle' => ($isBundle && $isConfigured) ? 'required|numeric|min:0' : ($request->filled('price_bundle') ? 'required|numeric|min:0' : 'nullable|numeric|min:0'),
            'update_price_3_months' => 'nullable|numeric|min:0',
            'update_price_6_months' => 'nullable|numeric|min:0',
            'update_price_12_months' => 'nullable|numeric|min:0',
            'passing_score' => 'required|integer|min:0|max:100',
            'question_count' => 'nullable|integer|min:0',
            'difficulty' => 'required|in:Associate,Professional,Expert',
            'exam_type' => 'required|in:MultipleChoice,MultiSelect,LabBased',
            'topics' => 'nullable', // string or array
            'description' => 'nullable|string',
            'demo_pdf' => 'nullable|file|mimes:pdf|max:20480',
            'full_pdf' => 'nullable|file|mimes:pdf|max:51200',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'meta_keywords' => 'nullable|string|max:255',
            'is_featured' => 'nullable|boolean',
            'sort_order' => 'nullable|integer|min:0',
            'admin_notes' => 'nullable|string|max:5000',
        ], [
            'price_pdf.required' => 'Enter a PDF price.',
            'price_pdf.min' => 'PDF price cannot be negative.',
            'price_engine.required' => 'Enter a simulator price.',
            'price_engine.min' => 'Simulator price cannot be negative.',
            'price_bundle.required' => 'Enter a bundle price.',
            'price_bundle.min' => 'Bundle price cannot be negative.',
        ]);

        $topicsArray = [];
        if (is_array($request->topics)) {
            $topicsArray = array_values(array_filter(array_map('trim', $request->topics)));
        } elseif ($request->filled('topics')) {
            $topicsArray = array_values(array_filter(array_map('trim', explode(',', $request->topics))));
        }

        $demoFilename = null;
        if ($request->hasFile('demo_pdf')) {
            $demoFile = $request->file('demo_pdf');
            $demoFilename = Str::slug($request->exam_code) . '-demo.pdf';
            try {
                Storage::disk('r2')->putFileAs('demos', $demoFile, $demoFilename);
            } catch (\Throwable $e) {
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
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::error('R2 upload failed for full PDF: ' . $e->getMessage());
            }
            Storage::disk('public')->putFileAs('full', $fullFile, $fullFilename);
        }

        $isActive = $request->input('action') === 'draft' ? false : ($request->has('is_active') ? true : false);
        $slug = $request->filled('slug') ? Str::slug($request->slug) : Str::slug($request->exam_code);

        $exam = Exam::create([
            'vendor_id' => $request->vendor_id,
            'exam_code' => $request->exam_code,
            'exam_name' => $request->exam_name,
            'header_title' => !empty(trim($request->header_title ?? '')) ? trim($request->header_title) : null,
            'slug' => $slug,
            'price_pdf' => $isPdf ? (float)$request->price_pdf : ((float)$request->price_pdf ?: 0),
            'price_engine' => $isEngine ? (float)$request->price_engine : ((float)$request->price_engine ?: 0),
            'price_bundle' => $isBundle && $request->filled('price_bundle') ? (float)$request->price_bundle : null,
            'is_pdf_available' => $isPdf,
            'is_engine_available' => $isEngine,
            'is_bundle_available' => $isBundle,
            'update_price_3_months' => $request->update_price_3_months ?? 0,
            'update_price_6_months' => $request->update_price_6_months ?? 10,
            'update_price_12_months' => $request->update_price_12_months ?? 20,
            'passing_score' => $request->passing_score,
            'question_count' => $request->filled('question_count') ? (int)$request->question_count : 0,
            'difficulty' => $request->difficulty,
            'exam_type' => $request->exam_type,
            'topics' => $topicsArray,
            'description' => $request->description,
            'is_active' => $isActive,
            'is_featured' => $request->has('is_featured'),
            'sort_order' => $request->filled('sort_order') ? (int)$request->sort_order : 0,
            'admin_notes' => $request->admin_notes,
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

        AuditLogService::log(
            'exam_created',
            "Created certification exam: {$exam->exam_code} - {$exam->exam_name}" . ($isActive ? ' [Published]' : ' [Draft]'),
            null,
            [
                'exam_id' => $exam->id,
                'exam_code' => $exam->exam_code,
                'is_active' => $isActive,
                'price_pdf' => $exam->price_pdf,
                'price_engine' => $exam->price_engine
            ]
        );

        $statusMsg = $isActive ? 'Exam created and published successfully.' : 'Exam saved as Draft successfully.';
        return redirect()->route('admin.exams.edit', $exam->id)->with('success', $statusMsg);
    }

    public function edit(int $id)
    {
        $exam = Exam::with(['certifications', 'vendor'])->findOrFail($id);
        $vendors = Vendor::where('is_active', true)->orderBy('name')->get();
        $certifications = Certification::orderBy('name')->get();
        
        $actualQuestionCount = $exam->questions()->count();
        $demoFileInfo = $this->getFileInfo($exam->demo_pdf_filename, 'demos');
        $fullFileInfo = $this->getFileInfo($exam->full_pdf_filename, 'full');

        return view('admin.exams.edit', compact(
            'exam',
            'vendors',
            'certifications',
            'actualQuestionCount',
            'demoFileInfo',
            'fullFileInfo'
        ));
    }

    public function update(Request $request, int $id)
    {
        $exam = Exam::findOrFail($id);

        $isConfigured = $request->has('availability_configured');
        $isPdf = $isConfigured ? $request->boolean('is_pdf_available') : ($request->has('is_pdf_available') ? $request->boolean('is_pdf_available') : (bool)$exam->is_pdf_available);
        $isEngine = $isConfigured ? $request->boolean('is_engine_available') : ($request->has('is_engine_available') ? $request->boolean('is_engine_available') : (bool)$exam->is_engine_available);
        $isBundle = $isConfigured ? $request->boolean('is_bundle_available') : ($request->has('is_bundle_available') ? $request->boolean('is_bundle_available') : (bool)$exam->is_bundle_available);

        if (!$isPdf && !$isEngine && !$isBundle) {
            return back()->withInput()->withErrors([
                'product_availability' => 'At least one purchase option must be enabled.'
            ]);
        }

        $request->validate([
            'vendor_id' => 'required|exists:vendors,id',
            'exam_code' => 'required|string|max:100|unique:exams,exam_code,' . $exam->id,
            'exam_name' => 'required|string|max:255',
            'header_title' => 'nullable|string|max:255',
            'slug' => 'nullable|string|max:255',
            'is_pdf_available' => 'nullable|boolean',
            'is_engine_available' => 'nullable|boolean',
            'is_bundle_available' => 'nullable|boolean',
            'price_pdf' => ($isPdf && ($isConfigured || $request->has('price_pdf'))) ? 'required|numeric|min:0' : 'nullable|numeric|min:0',
            'price_engine' => ($isEngine && ($isConfigured || $request->has('price_engine'))) ? 'required|numeric|min:0' : 'nullable|numeric|min:0',
            'price_bundle' => ($isBundle && $isConfigured) ? 'required|numeric|min:0' : ($request->filled('price_bundle') ? 'required|numeric|min:0' : 'nullable|numeric|min:0'),
            'update_price_3_months' => 'nullable|numeric|min:0',
            'update_price_6_months' => 'nullable|numeric|min:0',
            'update_price_12_months' => 'nullable|numeric|min:0',
            'passing_score' => 'required|integer|min:0|max:100',
            'question_count' => 'nullable|integer|min:0',
            'difficulty' => 'required|in:Associate,Professional,Expert',
            'exam_type' => 'required|in:MultipleChoice,MultiSelect,LabBased',
            'topics' => 'nullable',
            'description' => 'nullable|string',
            'demo_pdf' => 'nullable|file|mimes:pdf|max:20480',
            'full_pdf' => 'nullable|file|mimes:pdf|max:51200',
            'remove_demo_pdf' => 'nullable|boolean',
            'remove_full_pdf' => 'nullable|boolean',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'meta_keywords' => 'nullable|string|max:255',
            'is_featured' => 'nullable|boolean',
            'sort_order' => 'nullable|integer|min:0',
            'admin_notes' => 'nullable|string|max:5000',
        ], [
            'price_pdf.required' => 'Enter a PDF price.',
            'price_pdf.min' => 'PDF price cannot be negative.',
            'price_engine.required' => 'Enter a simulator price.',
            'price_engine.min' => 'Simulator price cannot be negative.',
            'price_bundle.required' => 'Enter a bundle price.',
            'price_bundle.min' => 'Bundle price cannot be negative.',
        ]);

        $topicsArray = [];
        if (is_array($request->topics)) {
            $topicsArray = array_values(array_filter(array_map('trim', $request->topics)));
        } elseif ($request->filled('topics')) {
            $topicsArray = array_values(array_filter(array_map('trim', explode(',', $request->topics))));
        }

        $isActive = $request->input('action') === 'draft' ? false : ($request->has('is_active') ? true : false);
        $slug = $request->filled('slug') ? Str::slug($request->slug) : Str::slug($request->exam_code);

        $updateData = [
            'vendor_id' => $request->vendor_id,
            'exam_code' => $request->exam_code,
            'exam_name' => $request->exam_name,
            'header_title' => !empty(trim($request->header_title ?? '')) ? trim($request->header_title) : null,
            'slug' => $slug,
            'price_pdf' => $isPdf ? ($request->has('price_pdf') ? (float)$request->price_pdf : (float)$exam->price_pdf) : 0,
            'price_engine' => $isEngine ? ($request->has('price_engine') ? (float)$request->price_engine : (float)$exam->price_engine) : 0,
            'price_bundle' => $isConfigured ? ($isBundle && $request->filled('price_bundle') ? (float)$request->price_bundle : null) : ($request->filled('price_bundle') ? (float)$request->price_bundle : $exam->price_bundle),
            'is_pdf_available' => $isPdf,
            'is_engine_available' => $isEngine,
            'is_bundle_available' => $isBundle,
            'update_price_3_months' => $request->update_price_3_months ?? 0,
            'update_price_6_months' => $request->update_price_6_months ?? 10,
            'update_price_12_months' => $request->update_price_12_months ?? 20,
            'passing_score' => $request->passing_score,
            'question_count' => $request->filled('question_count') ? (int)$request->question_count : 0,
            'difficulty' => $request->difficulty,
            'exam_type' => $request->exam_type,
            'topics' => $topicsArray,
            'description' => $request->description,
            'is_active' => $isActive,
            'is_featured' => $request->has('is_featured'),
            'sort_order' => $request->filled('sort_order') ? (int)$request->sort_order : 0,
            'admin_notes' => $request->admin_notes,
            'meta_title' => $request->meta_title,
            'meta_description' => $request->meta_description,
            'meta_keywords' => $request->meta_keywords,
            'last_updated_at' => now(),
        ];

        // Handle Removal of Demo PDF
        if ($request->boolean('remove_demo_pdf')) {
            if ($exam->demo_pdf_filename) {
                Storage::disk('public')->delete('demos/' . $exam->demo_pdf_filename);
            }
            $updateData['demo_pdf_filename'] = null;
        }

        // Handle Removal of Full Access PDF
        if ($request->boolean('remove_full_pdf')) {
            if ($exam->full_pdf_filename) {
                Storage::disk('public')->delete('full/' . $exam->full_pdf_filename);
            }
            $updateData['full_pdf_filename'] = null;
        }

        if ($request->hasFile('demo_pdf')) {
            $demoFile = $request->file('demo_pdf');
            $demoFilename = Str::slug($request->exam_code) . '-demo.pdf';
            try {
                Storage::disk('r2')->putFileAs('demos', $demoFile, $demoFilename);
            } catch (\Throwable $e) {
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
            } catch (\Throwable $e) {
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

        $changedFields = array_keys($exam->getChanges());
        if (!empty($changedFields)) {
            AuditLogService::log(
                'exam_updated',
                "Updated exam {$exam->exam_code} (" . implode(', ', $changedFields) . ")",
                null,
                ['exam_id' => $exam->id, 'changes' => $exam->getChanges()]
            );
        }

        // Retrieve all users who have access to this exam
        $purchasedUserIds = UserExam::where('exam_id', $exam->id)
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

        $statusMsg = $isActive ? 'Exam updated and published successfully.' : 'Exam updated and set to Draft (Hidden).';
        return redirect()->route('admin.exams.edit', $exam->id)->with('success', $statusMsg);
    }

    /**
     * Download or preview PDF in secure admin session.
     */
    public function downloadPdf(Exam $exam, string $type)
    {
        $filename = $type === 'demo' ? $exam->demo_pdf_filename : $exam->full_pdf_filename;
        $folder = $type === 'demo' ? 'demos' : 'full';

        if (!$filename) {
            return back()->with('error', 'No PDF file configured for this exam.');
        }

        $path = $folder . '/' . $filename;

        if (Storage::disk('public')->exists($path)) {
            return Storage::disk('public')->response($path, $filename, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="' . $filename . '"',
            ]);
        }

        return back()->with('error', 'The requested PDF file was not found in local storage.');
    }

    public function destroy(int $id)
    {
        $exam = Exam::findOrFail($id);

        $hasPurchases = UserExam::where('exam_id', $exam->id)->exists();
        $hasOrders = OrderItem::where('item_type', 'exam')->where('item_id', $exam->id)->exists();

        if ($hasPurchases || $hasOrders) {
            return back()->with('error', "Cannot delete exam {$exam->exam_code}: active customer purchases or order items depend on this exam. You can deactivate it by switching Status to Draft.");
        }

        $code = $exam->exam_code;
        $name = $exam->exam_name;
        $examId = $exam->id;

        $exam->delete();

        AuditLogService::log(
            'exam_deleted',
            "Deleted certification exam {$code} ({$name})",
            null,
            ['exam_id' => $examId, 'exam_code' => $code]
        );

        return redirect()->route('admin.exams.index')->with('success', "Exam {$code} deleted successfully.");
    }

    /**
     * Helper to read PDF metadata from local storage.
     */
    protected function getFileInfo(?string $filename, string $folder): ?array
    {
        if (!$filename) {
            return null;
        }

        $path = $folder . '/' . $filename;
        $sizeBytes = 0;
        $lastModified = null;
        $exists = false;

        if (Storage::disk('public')->exists($path)) {
            $exists = true;
            try {
                $sizeBytes = Storage::disk('public')->size($path);
                $lastModified = date('M d, Y g:i A', Storage::disk('public')->lastModified($path));
            } catch (\Throwable $e) {
                // Ignore storage inspection errors
            }
        }

        return [
            'filename' => $filename,
            'exists' => $exists,
            'size_formatted' => $sizeBytes > 0 ? round($sizeBytes / (1024 * 1024), 2) . ' MB' : 'Available',
            'last_modified' => $lastModified ?: 'Recently',
        ];
    }
}
