<?php

namespace Tests\Feature;

use App\Models\Exam;
use App\Models\Question;
use App\Models\TestAnswer;
use App\Models\TestAttempt;
use App\Models\User;
use App\Models\UserExam;
use App\Models\Vendor;
use App\Services\ExamImport\ExamFileParser;
use App\Services\ExamImport\ExamFileValidator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ExamFileImportTest extends TestCase
{
    use RefreshDatabase;

    /** 1x1 PNG */
    protected const PNG = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNkYPhfDwAChwGA60e6kgAAAABJRU5ErkJggg==';

    protected User $admin;
    protected Exam $exam;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        Storage::fake('local');

        $this->admin = User::factory()->create(['role' => 'admin']);
        $vendor = Vendor::create(['name' => 'Microsoft', 'slug' => 'microsoft']);
        $this->exam = Exam::create([
            'vendor_id' => $vendor->id,
            'exam_code' => 'PL-900',
            'exam_name' => 'Microsoft Power Platform Fundamentals',
            'slug' => 'pl-900',
            'is_active' => true,
            'passing_score' => 70,
            'question_count' => 0,
        ]);
    }

    /** A practice-test HTML in the same shape as the PL-900 file / exam-template.html. */
    protected function sampleHtml(array $overrideAnswers = []): string
    {
        $questions = [
            ['id' => 1, 'topic' => 'Topic 1', 'type' => 'multiple-choice', 'question' => ['Which app should users install?'],
                'options' => [['label' => 'A', 'text' => 'Remote Assist'], ['label' => 'B', 'text' => 'Dynamics 365 for Phones']],
                'images' => [], 'boxCount' => 0, 'yesNo' => false, 'ia' => null],
            ['id' => 2, 'topic' => 'Topic 1', 'type' => 'multiple-select', 'question' => ['What are two characteristics? Choose two.'],
                'options' => [['label' => 'A', 'text' => 'a'], ['label' => 'B', 'text' => 'b'], ['label' => 'C', 'text' => 'c']],
                'images' => [], 'boxCount' => 0, 'yesNo' => false, 'ia' => null],
            ['id' => 3, 'topic' => 'Topic 2', 'type' => 'hotspot', 'question' => ['For each statement select Yes or No.'],
                'options' => [], 'images' => ['image1'], 'boxCount' => 0, 'yesNo' => false,
                'ia' => ['kind' => 'yesno', 'rows' => [['label' => 'Statement one'], ['label' => 'Statement two']]]],
            ['id' => 4, 'topic' => 'Topic 2', 'type' => 'drag-drop', 'question' => ['Match the tools.'],
                'options' => [], 'images' => [], 'boxCount' => 0, 'yesNo' => false,
                'ia' => ['kind' => 'match', 'pool' => ['Power BI', 'Power Apps'], 'rows' => [['label' => 'Build apps'], ['label' => 'Reports']]]],
            ['id' => 5, 'topic' => 'Topic 3', 'type' => 'hotspot', 'question' => ['Select Yes or No for each box in the exhibit.'],
                'options' => [], 'images' => ['image1'], 'boxCount' => 2, 'yesNo' => true, 'ia' => null],
            ['id' => 6, 'topic' => 'Topic 3', 'type' => 'drag-drop', 'question' => ['Complete the answer area in the exhibit.'],
                'options' => [], 'images' => ['image1'], 'boxCount' => 0, 'yesNo' => false, 'ia' => null],
        ];
        $answers = $overrideAnswers + [
            '1' => ['correctAnswer' => ['B'], 'boxes' => [], 'explanation' => ['Phones app.'], 'reference' => ['https://learn.microsoft.com/x'], 'answerImages' => [], 'rowAnswers' => null],
            '2' => ['correctAnswer' => ['A', 'C'], 'boxes' => [], 'explanation' => [], 'reference' => [], 'answerImages' => [], 'rowAnswers' => null],
            '3' => ['correctAnswer' => null, 'boxes' => [], 'explanation' => ['Because.'], 'reference' => [], 'answerImages' => ['image2'], 'rowAnswers' => ['Yes', 'No']],
            '4' => ['correctAnswer' => null, 'boxes' => [], 'explanation' => ['x'], 'reference' => [], 'answerImages' => [], 'rowAnswers' => ['Power Apps', 'Power BI']],
            '5' => ['correctAnswer' => null, 'boxes' => [['box' => 1, 'value' => 'Yes.'], ['box' => 2, 'value' => 'No']], 'explanation' => ['y'], 'reference' => [], 'answerImages' => [], 'rowAnswers' => null],
            '6' => ['correctAnswer' => null, 'boxes' => [], 'explanation' => [], 'reference' => ['https://learn.microsoft.com/y'], 'answerImages' => ['image2'], 'rowAnswers' => null],
        ];

        return '<!DOCTYPE html><html><body><script>' . "\n"
            . 'const EXAM_META = ' . json_encode(['code' => 'PL-900', 'title' => 'Power Platform']) . ";\n"
            . 'const QUESTIONS = ' . json_encode($questions) . ";\n"
            . 'const ANSWERS_B64 = "' . base64_encode(json_encode($answers)) . "\";\n"
            . 'const QIMG = ' . json_encode(['image1' => self::PNG]) . ";\n"
            . 'const AIMG = ' . json_encode(['image2' => self::PNG]) . ";\n"
            . '</script><script>(function(){ const TOTAL = QUESTIONS.length; })();</script></body></html>';
    }

    protected function importSample(string $html, string $mode = 'replace')
    {
        $preview = $this->actingAs($this->admin)->post(route('admin.questions.import.preview'), [
            'exam_id' => $this->exam->id,
            'exam_file' => UploadedFile::fake()->createWithContent('PL-900-practice-test.html', $html),
        ]);
        $preview->assertOk();
        $token = $preview->viewData('token');

        return $this->actingAs($this->admin)->post(route('admin.questions.import.store'), ['token' => $token, 'mode' => $mode]);
    }

    public function test_admin_can_preview_and_import_a_practice_test_html(): void
    {
        $this->actingAs($this->admin)->get(route('admin.questions.import'))->assertOk()->assertSee('Copy prompt');

        $this->importSample($this->sampleHtml())
            ->assertRedirect(route('admin.questions.index', ['exam_id' => $this->exam->id]))
            ->assertSessionHas('success');

        $this->assertSame(6, Question::where('exam_id', $this->exam->id)->count());
        $this->assertSame(6, $this->exam->fresh()->question_count);

        $types = Question::orderBy('id')->pluck('question_type')->all();
        $this->assertSame(['single_choice', 'multiple_choice', 'hotspot', 'drag_drop', 'hotspot', 'drag_drop'], $types);

        $q1 = Question::orderBy('id')->first();
        $this->assertSame('B', $q1->correct_option);
        $this->assertSame(['B'], $q1->question_data['engine']['answer']['correctAnswer']);

        $q3 = Question::orderBy('id')->skip(2)->first();
        $this->assertSame(['Yes', 'No'], $q3->question_data['engine']['answer']['rowAnswers']);
        $img = $q3->question_data['engine']['images'][0];
        $this->assertStringStartsWith('/storage/questions/pl-900/', $img);
        Storage::disk('public')->assertExists(substr($img, strlen('/storage/')));
        // identical exhibit and answer images are stored once
        $this->assertCount(1, Storage::disk('public')->files('questions/pl-900'));
    }

    public function test_replace_mode_removes_old_questions_and_append_keeps_them(): void
    {
        $this->importSample($this->sampleHtml());
        $this->importSample($this->sampleHtml(), 'append');
        $this->assertSame(12, Question::count());

        $this->importSample($this->sampleHtml(), 'replace');
        $this->assertSame(6, Question::count());
    }

    public function test_questions_with_errors_are_reported_and_skipped(): void
    {
        $html = $this->sampleHtml([
            '1' => ['correctAnswer' => ['E'], 'boxes' => [], 'explanation' => ['x'], 'reference' => [], 'answerImages' => [], 'rowAnswers' => null],
        ]);

        $preview = $this->actingAs($this->admin)->post(route('admin.questions.import.preview'), [
            'exam_id' => $this->exam->id,
            'exam_file' => UploadedFile::fake()->createWithContent('bad.html', $html),
        ]);
        $preview->assertOk()->assertSee('Correct answer &quot;E&quot; is not one of the options', false);

        $this->actingAs($this->admin)->post(route('admin.questions.import.store'), ['token' => $preview->viewData('token'), 'mode' => 'replace']);
        $this->assertSame(5, Question::count());
    }

    public function test_admin_edits_after_import_show_up_in_the_engine(): void
    {
        $this->importSample($this->sampleHtml());
        $q1 = Question::orderBy('id')->first();
        $this->assertSame(['Which app should users install?'], \App\Services\TestEngine\EngineQuestion::payload($q1)['question']);

        Question::saveFromUniversalModel([
            'exam_id' => $this->exam->id, 'question_type' => 'single_choice', 'question_text' => '<p>Which mobile app should users install?</p>',
            'explanation' => '<p>Phones app.</p>', 'is_active' => true, 'status' => 'published',
            'options' => [['key' => 'A', 'text' => 'Remote Assist'], ['key' => 'B', 'text' => 'Dynamics 365 for Phones']],
            'correct_answers' => ['A'],
        ], $q1);

        $payload = \App\Services\TestEngine\EngineQuestion::payload($q1->fresh());
        $this->assertStringContainsString('Which mobile app', $payload['questionHtml']);
        $this->assertSame(['A'], $payload['answer']['correctAnswer']);
    }

    public function test_file_without_exam_data_is_rejected(): void
    {
        $this->actingAs($this->admin)->post(route('admin.questions.import.preview'), [
            'exam_id' => $this->exam->id,
            'exam_file' => UploadedFile::fake()->createWithContent('page.html', '<html><body>Hello</body></html>'),
        ])->assertRedirect()->assertSessionHas('error');

        $this->assertSame(0, Question::count());
    }

    public function test_parser_accepts_json_with_inline_answers(): void
    {
        $json = json_encode(['meta' => ['code' => 'PL-900'], 'questions' => [[
            'id' => 1, 'type' => 'Multiple Choice', 'question' => 'Pick one', 'options' => [['label' => 'A', 'text' => 'x'], ['label' => 'B', 'text' => 'y']],
            'answer' => ['correctAnswer' => ['A']],
        ]]]);
        $parsed = (new ExamFileParser)->parse($json);
        $this->assertSame('multiple-choice', $parsed['questions'][0]['type']);
        $this->assertSame(['Pick one'], $parsed['questions'][0]['question']);
        $this->assertSame([], (new ExamFileValidator)->validate($parsed)['errors']);
    }

    protected function startAttempt(string $mode): TestAttempt
    {
        $this->importSample($this->sampleHtml());
        $student = User::factory()->create();
        UserExam::create(['user_id' => $student->id, 'exam_id' => $this->exam->id, 'access_type' => 'engine', 'purchased_at' => now()]);
        $this->actingAs($student);

        $this->post(route('dashboard.test-engine.start', $this->exam->id), ['mode' => $mode, 'count' => 6, 'order' => 'sequential'])
            ->assertRedirect();

        return TestAttempt::latest('id')->firstOrFail();
    }

    protected function save(TestAttempt $attempt, int $number, array $response)
    {
        $qid = TestAnswer::where('attempt_id', $attempt->id)->orderBy('id')->skip($number - 1)->value('question_id');
        return $this->postJson(route('dashboard.test-engine.answer'), ['attempt_id' => $attempt->id, 'question_id' => $qid, 'response' => $response]);
    }

    public function test_practice_session_renders_engine_and_grades_every_question_type(): void
    {
        $attempt = $this->startAttempt('practice');

        $page = $this->get(route('dashboard.test-engine.session', $attempt->id));
        $page->assertOk()->assertSee('window.ETB_ENGINE', false)->assertSee('test-engine/engine.js', false);
        $config = $page->viewData('config');
        $this->assertCount(6, $config['questions']);
        $this->assertArrayNotHasKey('answer', $config['questions'][0]);
        $this->assertNotNull($config['answers']);

        $this->save($attempt, 1, ['sel' => ['B'], 'status' => 'correct'])->assertJson(['status' => 'correct']);
        $this->save($attempt, 2, ['sel' => ['A'], 'status' => 'correct'])->assertJson(['status' => 'incorrect']); // server re-grades
        $this->save($attempt, 3, ['rows' => ['Yes', 'No'], 'status' => 'correct'])->assertJson(['status' => 'correct']);
        $this->save($attempt, 4, ['rows' => ['Power BI', 'Power BI'], 'status' => 'incorrect'])->assertJson(['status' => 'incorrect']);
        $this->save($attempt, 5, ['boxes' => ['1' => 'Yes', '2' => 'No'], 'status' => 'correct'])->assertJson(['status' => 'correct']);
        $this->save($attempt, 6, ['status' => 'correct', 'self' => true])->assertJson(['status' => 'correct']); // self-assessed

        $this->post(route('dashboard.test-engine.submit', $attempt->id), ['time_taken' => 60])
            ->assertRedirect(route('dashboard.test-engine.results', $attempt->id));
        $attempt->refresh();
        $this->assertSame(4, $attempt->correct);
        $this->assertSame(6, $attempt->answered);

        $this->get(route('dashboard.test-engine.results', $attempt->id))->assertOk()->assertSee('Power Apps');
    }

    public function test_exam_mode_ships_no_answers_and_hides_correctness(): void
    {
        $attempt = $this->startAttempt('exam');

        $config = $this->get(route('dashboard.test-engine.session', $attempt->id))->assertOk()->viewData('config');
        $this->assertNull($config['answers']);

        $this->save($attempt, 1, ['sel' => ['B'], 'status' => 'answered'])->assertJson(['status' => null]);
        $this->assertTrue(TestAnswer::where('attempt_id', $attempt->id)->orderBy('id')->first()->is_correct);

        // A reload shows the question as answered, not as correct
        $config = $this->get(route('dashboard.test-engine.session', $attempt->id))->viewData('config');
        $firstQid = $config['questions'][0]['qid'];
        $this->assertSame('answered', ((array) $config['state'])[$firstQid]['status']);
    }

    public function test_student_cannot_save_to_someone_elses_attempt(): void
    {
        $attempt = $this->startAttempt('practice');
        $this->actingAs(User::factory()->create());
        $this->save($attempt, 1, ['sel' => ['B'], 'status' => 'correct'])->assertStatus(403);
    }
}
