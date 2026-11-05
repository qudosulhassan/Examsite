<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\Vendor;
use App\Models\Question;
use App\Models\Review;
use App\Models\Redirect;

use App\Services\ThemeManager;
use App\Support\ExamTracks;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class ExamController extends Controller
{
    /**
     * Display the specific exam page details (/exams/{vendor}/{slug}).
     */
    public function show(string $vendor, string $slug)
    {
        $vendorClean = strtolower(trim($vendor));
        $slugClean = strtolower(trim($slug));

        // 1. Strictly resolve Vendor
        $vendorModel = Vendor::where('slug', $vendorClean)
            ->where('is_active', true)
            ->first();

        if (!$vendorModel) {
            abort(404);
        }

        // Root-style sites append "-exam" to the public slug; accept the bare slug too.
        $baseSlug = Exam::usesRootUrls() ? preg_replace('/-exam$/', '', $slugClean) : $slugClean;

        // 2. Query Exam strictly belonging to this vendor
        $exam = Exam::where('is_active', true)
            ->where('vendor_id', $vendorModel->id)
            ->where(function ($q) use ($slugClean, $baseSlug) {
                $q->where('slug', $slugClean)
                  ->orWhere('slug', $baseSlug)
                  ->orWhere('exam_code', $slugClean)
                  ->orWhere('exam_code', $baseSlug);
            })
            ->with(['vendor', 'overlays'])
            ->first();

        if (!$exam) {
            abort(404);
        }

        // Verify catalog visibility via SiteContext service
        $siteContext = app(\App\Services\SiteContext::class);
        if (!$siteContext->isExamVisible($exam)) {
            abort(404);
        }
        $overlay = $siteContext->getOverlayForExam($exam);

        $canonicalVendor = $exam->vendor ? $exam->vendor->slug : $vendorModel->slug;
        $canonicalSlug = Exam::urlSlug($exam->slug);

        // 3. Canonical 301 URL redirect if URL does not match canonical /exams/{vendor}/{slug}
        if ($vendor !== $canonicalVendor || $slug !== $canonicalSlug) {
            return redirect(Exam::urlFor($canonicalVendor, $exam->slug), 301);
        }

        // Get the first 10 well-formed single-choice questions for the free preview.
        // The A-D radio card UI only supports classic single-choice questions, so
        // drag-drop/hotspot/multi-select types (and rows with a stray non A-D answer
        // key) are excluded here - otherwise they render as blank/unanswerable cards.
        $sampleQuestions = Question::where('exam_id', $exam->id)
            ->where('is_active', true)
            ->where('question_type', 'single_choice')
            ->with(['options', 'answers'])
            ->orderBy('id')
            ->limit(50)
            ->get()
            ->filter(function ($question) {
                return $question->answers->count() === 1
                    && in_array($question->answers->first()->answer_value, ['A', 'B', 'C', 'D'], true);
            })
            ->take(10)
            ->values();

        // Get approved customer reviews
        $reviews = Review::where('exam_id', $exam->id)
            ->where('is_approved', true)
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->get();

        return app(ThemeManager::class)->view('exams.show', compact('exam', 'sampleQuestions', 'reviews', 'overlay'));
    }

    /**
     * Root-style sites only: 301 the legacy /exams/{vendor}/{slug} URL to /{vendor}/{slug}-exam.
     */
    public function showLegacy(string $vendor, string $slug)
    {
        $vendorClean = strtolower(trim($vendor));
        $slugClean = strtolower(trim($slug));
        $baseSlug = preg_replace('/-exam$/', '', $slugClean);

        $vendorModel = Vendor::where('slug', $vendorClean)->first();
        if ($vendorModel) {
            $exam = Exam::where('vendor_id', $vendorModel->id)
                ->where(function ($q) use ($slugClean, $baseSlug) {
                    $q->where('slug', $slugClean)
                      ->orWhere('slug', $baseSlug)
                      ->orWhere('exam_code', $slugClean)
                      ->orWhere('exam_code', $baseSlug);
                })
                ->first();

            if ($exam) {
                return redirect(Exam::urlFor($exam->vendor ? $exam->vendor->slug : $vendorModel->slug, $exam->slug), 301);
            }
        }

        return redirect(Exam::urlFor($vendorClean, $slugClean), 301);
    }

    // ===================================================================
    // Hub style (single-vendor sites): guide, free questions, plans, tracks
    // ===================================================================

    private const FREE_QUESTIONS_PER_PAGE = 5;

    /**
     * Resolve an active, visible exam from a hub-style slug. Wrong case or an exam code instead of
     * the slug 301s to the canonical URL, so only one URL per page is ever indexed.
     */
    private function findHubExam(string $slug, ?string $suffixRoute = null)
    {
        $clean = strtolower(trim($slug));

        $exam = Exam::where('is_active', true)
            ->where(function ($q) use ($clean) {
                $q->where('slug', $clean)->orWhereRaw('LOWER(exam_code) = ?', [$clean]);
            })
            ->with(['vendor', 'overlays', 'certifications'])
            ->first();

        if (!$exam || !app(\App\Services\SiteContext::class)->isExamVisible($exam)) {
            abort(404);
        }

        if ($slug !== $exam->slug) {
            $target = $suffixRoute ? route($suffixRoute, ['slug' => $exam->slug]) : Exam::urlFor($exam->vendor->slug, $exam->slug);
            abort(redirect($target, 301));
        }

        return $exam;
    }

    /**
     * The free practice questions: well-formed single-choice items only (the A-D card UI cannot
     * render drag-drop/hotspot). Everything returned is shown in full to users and crawlers alike.
     */
    private function freeQuestionsFor(Exam $exam): Collection
    {
        return Question::where('exam_id', $exam->id)
            ->where('is_active', true)
            ->where('question_type', 'single_choice')
            ->with(['options', 'answers'])
            ->orderBy('id')
            ->limit(60)
            ->get()
            ->filter(fn ($question) => $question->answers->count() === 1
                && in_array($question->answers->first()->answer_value, ['A', 'B', 'C', 'D'], true))
            ->take((int) config('site.free_questions', 10))
            ->values();
    }

    private function approvedReviews(Exam $exam): Collection
    {
        return Review::where('exam_id', $exam->id)->where('is_approved', true)->with('user')->orderBy('created_at', 'desc')->get();
    }

    /** Other exams of the same track first, then the rest, max 4. */
    private function relatedExams(Exam $exam): Collection
    {
        $prefix = strtoupper(strtok((string) $exam->exam_code, '-') ?: '');

        $others = Exam::where('is_active', true)->where('id', '!=', $exam->id)->orderBy('exam_code')->get();
        $same = $others->filter(fn ($e) => strtoupper(strtok((string) $e->exam_code, '-') ?: '') === $prefix);

        return $same->concat($others->diff($same))->take(4)->values();
    }

    private function vendorSlugFor(Exam $exam): string
    {
        return (string) ($exam->vendor->slug ?? '');
    }

    /** Guide page: /exams/{slug} */
    public function showHub(string $slug)
    {
        $exam = $this->findHubExam($slug);

        $freeQuestions = $this->freeQuestionsFor($exam);
        $track = ExamTracks::forExam((string) $exam->exam_code, $this->vendorSlugFor($exam));

        return app(ThemeManager::class)->view('exams.hub', [
            'exam' => $exam,
            'freeCount' => $freeQuestions->count(),
            'bankCount' => Question::where('exam_id', $exam->id)->where('is_active', true)->count(),
            'reviews' => $this->approvedReviews($exam),
            'related' => $this->relatedExams($exam),
            'track' => $track,
            'overlay' => app(\App\Services\SiteContext::class)->getOverlayForExam($exam),
        ]);
    }

    /** Free questions: /exams/{slug}/free-practice-questions?page=N (5 per page) */
    public function questions(Request $request, string $slug)
    {
        $exam = $this->findHubExam($slug, 'exams.questions');

        $free = $this->freeQuestionsFor($exam);
        $perPage = max(1, $free->count());   // every free question on one page
        $lastPage = max(1, (int) ceil($free->count() / $perPage));
        $page = max(1, (int) $request->query('page', 1));

        if ($page > $lastPage) {
            abort(404);
        }

        return app(ThemeManager::class)->view('exams.questions', [
            'exam' => $exam,
            'questions' => $free->forPage($page, $perPage)->values(),
            'firstNumber' => ($page - 1) * $perPage + 1,
            'page' => $page,
            'lastPage' => $lastPage,
            'freeCount' => $free->count(),
            'bankCount' => Question::where('exam_id', $exam->id)->where('is_active', true)->count(),
            'related' => $this->relatedExams($exam),
            'track' => ExamTracks::forExam((string) $exam->exam_code, $this->vendorSlugFor($exam)),
        ]);
    }

    /** Plans / purchase page: /exams/{slug}/plans */
    public function plans(string $slug)
    {
        $exam = $this->findHubExam($slug, 'exams.plans');

        $periods = [3, 6, 12];
        $addons = [];
        foreach (['pdf', 'engine_single', 'combo'] as $type) {
            foreach ($periods as $months) {
                $addons[$type][$months] = (float) $exam->resolveUpdatePrice($type, $months);
            }
        }

        return app(ThemeManager::class)->view('exams.plans', [
            'exam' => $exam,
            'prices' => [
                'pdf' => (float) $exam->resolved_price_pdf,
                'engine_single' => (float) $exam->resolved_price_engine,
                'combo' => (float) $exam->effective_bundle_price,
            ],
            'addons' => $addons,
            'freeCount' => $this->freeQuestionsFor($exam)->count(),
            'bankCount' => Question::where('exam_id', $exam->id)->where('is_active', true)->count(),
            'stripeEnabled' => Setting::get('payment_gateway_stripe_enabled', '1') === '1',
            'paypalEnabled' => Setting::get('payment_gateway_paypal_enabled', '1') === '1',
            'reviews' => $this->approvedReviews($exam),
            'related' => $this->relatedExams($exam),
            'track' => ExamTracks::forExam((string) $exam->exam_code, $this->vendorSlugFor($exam)),
        ]);
    }

    /** /exams/{vendor}/{slug} (the older style) -> the hub URL, or 404. */
    public function showLegacyHub(string $vendor, string $slug)
    {
        $exam = Exam::where('is_active', true)->where('slug', strtolower(trim($slug)))->with('vendor')->first();

        if (!$exam || !app(\App\Services\SiteContext::class)->isExamVisible($exam)) {
            abort(404);
        }

        return redirect(Exam::urlFor($exam->vendor->slug, $exam->slug), 301);
    }

    /** Track landing page: /tracks/{track} */
    public function track(string $track)
    {
        $vendorSlug = (string) ((array) config('site.vendors', []))[0] ?? '';
        $exams = Exam::where('is_active', true)->with(['vendor', 'overlays'])->orderBy('exam_code')->get();

        $group = collect(ExamTracks::group($exams, $vendorSlug))->firstWhere('slug', $track);

        if (!$group) {
            abort(404);
        }

        return app(ThemeManager::class)->view('tracks.show', [
            'track' => $group,
            'allTracks' => ExamTracks::group($exams, $vendorSlug),
            'vendorSlug' => $vendorSlug,
        ]);
    }
}
