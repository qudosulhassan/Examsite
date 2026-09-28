<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Services\ExamImport\ExamFileParser;
use App\Services\ExamImport\ExamFileValidator;
use App\Services\ExamImport\ExamImporter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use InvalidArgumentException;

/**
 * Admin → Questions → Import Exam File.
 *
 * Flow: upload the practice-test HTML (or JSON) made with the Claude conversion prompt →
 * preview with checks → confirm → questions are saved and appear in the test engine.
 */
class QuestionImportController extends Controller
{
    protected const STAGING_DIR = 'exam-imports';

    public function create()
    {
        $exams = Exam::with('vendor')->withCount('questions')->orderBy('exam_code')->get();
        $prompt = file_get_contents(resource_path('exam-converter/claude-prompt.md'));
        // Filled into the prompt's {EXAM_CODE} / {EXAM_NAME} / {VENDOR} placeholders on the page
        $examInfo = $exams->mapWithKeys(fn ($e) => [$e->id => [
            'code' => $e->exam_code,
            'name' => $e->exam_name,
            'vendor' => $e->vendor?->name ?? '',
        ]]);

        return view('admin.questions.import', compact('exams', 'prompt', 'examInfo'));
    }

    public function template()
    {
        return response()->download(
            resource_path('exam-converter/exam-template.html'),
            'exam-template.html',
            ['Content-Type' => 'text/html; charset=UTF-8']
        );
    }

    public function preview(Request $request, ExamFileParser $parser, ExamFileValidator $validator)
    {
        $request->validate([
            'exam_id'   => 'required|exists:exams,id',
            'exam_file' => 'required|file|max:102400',
        ], [
            'exam_file.max' => 'The file is larger than 100 MB.',
        ]);

        $file = $request->file('exam_file');
        $ext = strtolower($file->getClientOriginalExtension());
        if (!in_array($ext, ['html', 'htm', 'json'], true)) {
            return back()->withInput()->with('error', 'Upload the .html practice-test file (or a .json file) that Claude produced. PDF and Word files are converted in Claude first — see step 1 on this page.');
        }

        try {
            $parsed = $parser->parse(file_get_contents($file->getRealPath()));
        } catch (InvalidArgumentException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        $report = $validator->validate($parsed);
        $exam = Exam::withCount('questions')->findOrFail($request->exam_id);

        $token = (string) Str::uuid();
        $this->cleanOldStagedFiles();
        Storage::disk('local')->put(self::STAGING_DIR . "/{$token}.json", json_encode([
            'exam_id'   => $exam->id,
            'file_name' => $file->getClientOriginalName(),
            'parsed'    => $parsed,
            'bad_ids'   => $report['stats']['bad_ids'],
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

        $codeMismatch = $parsed['meta']['code']
            && $this->normalizeCode($parsed['meta']['code']) !== $this->normalizeCode($exam->exam_code);

        return view('admin.questions.import-preview', [
            'exam'         => $exam,
            'token'        => $token,
            'fileName'     => $file->getClientOriginalName(),
            'parsed'       => $parsed,
            'report'       => $report,
            'codeMismatch' => $codeMismatch,
        ]);
    }

    public function store(Request $request, ExamImporter $importer)
    {
        $request->validate([
            'token' => 'required|uuid',
            'mode'  => 'required|in:replace,append',
        ]);

        $path = self::STAGING_DIR . '/' . $request->token . '.json';
        if (!Storage::disk('local')->exists($path)) {
            return redirect()->route('admin.questions.import')->with('error', 'This upload has expired. Please upload the file again.');
        }

        $staged = json_decode(Storage::disk('local')->get($path), true);
        $exam = Exam::findOrFail($staged['exam_id']);

        $result = $importer->import($exam, $staged['parsed'], $request->mode === 'replace', $staged['bad_ids'], $staged['file_name']);
        Storage::disk('local')->delete($path);

        $msg = "Imported {$result['imported']} questions into {$exam->exam_code}";
        if ($result['deleted']) {
            $msg .= " (replaced {$result['deleted']} old questions)";
        }
        if ($result['skipped']) {
            $msg .= ". Skipped {$result['skipped']} questions that had errors";
        }

        return redirect()->route('admin.questions.index', ['exam_id' => $exam->id])->with('success', $msg . '.');
    }

    protected function normalizeCode(string $code): string
    {
        return strtolower(preg_replace('/[^a-z0-9]/i', '', $code));
    }

    /** Uploads that were previewed but never confirmed are removed after a day. */
    protected function cleanOldStagedFiles(): void
    {
        $disk = Storage::disk('local');
        foreach ($disk->files(self::STAGING_DIR) as $f) {
            if ($disk->lastModified($f) < now()->subDay()->getTimestamp()) {
                $disk->delete($f);
            }
        }
    }
}
