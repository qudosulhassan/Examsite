<?php

namespace Tests\Feature;

use App\Models\Exam;
use App\Models\Question;
use App\Models\QuestionAnswer;
use App\Models\QuestionOption;
use App\Models\Redirect;
use App\Models\Site;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

/**
 * The single-vendor site's exam pages: /exams/{slug}, /exams/{slug}/free-practice-questions and
 * /exams/{slug}/plans. The URL style is chosen when the app boots, so these tests boot with it on.
 */
class HubExamPagesTest extends TestCase
{
    use RefreshDatabase;

    public function createApplication()
    {
        putenv('EXAM_URL_STYLE=hub');
        $_ENV['EXAM_URL_STYLE'] = $_SERVER['EXAM_URL_STYLE'] = 'hub';

        return parent::createApplication();
    }

    protected function tearDown(): void
    {
        $this->artisan('view:clear');
        parent::tearDown();

        putenv('EXAM_URL_STYLE');
        unset($_ENV['EXAM_URL_STYLE'], $_SERVER['EXAM_URL_STYLE']);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('view:clear');
        Cache::flush();

        foreach ([1 => ['ExamTopicsBase', 'examtopicsbase'], 3 => ['AzureExamsHub', 'stackcert']] as $id => [$name, $code]) {
            if (! Site::query()->whereKey($id)->exists()) {
                Site::query()->insert(['id' => $id, 'name' => $name, 'code' => $code, 'default_theme' => 'default', 'default_locale' => 'en', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()]);
            }
        }

        config([
            'site.id' => 3, 'site.mode' => 'standalone', 'site.catalog_mode' => 'all', 'site.vendors' => ['microsoft'],
            'site.avoid_dumps' => true, 'site.show_guarantee' => false, 'site.free_questions' => 7,
            'app.name' => 'AzureExamsHub', 'app.url' => 'http://stackcert.test',
        ]);
        Vendor::flushSiteVendorCache();
        app('view')->getFinder()->prependLocation(resource_path('views-sites/stackcert'));
    }

    private function exam(string $code = 'DP-800', int $freeQuestions = 7): Exam
    {
        $ms = Vendor::where('slug', 'microsoft')->first() ?: Vendor::factory()->create(['slug' => 'microsoft', 'name' => 'Microsoft', 'is_active' => true]);
        Vendor::flushSiteVendorCache();

        $exam = Exam::factory()->create([
            'vendor_id' => $ms->id, 'exam_code' => $code, 'exam_name' => 'SQL AI Developer', 'slug' => strtolower($code), 'is_active' => true,
            'price_pdf' => 30, 'price_engine' => 40, 'price_bundle' => 60, 'is_pdf_available' => true, 'is_engine_available' => true,
            'last_updated_at' => '2026-09-04 10:00:00',
        ]);

        for ($i = 1; $i <= $freeQuestions; $i++) {
            $q = Question::create(['exam_id' => $exam->id, 'question_type' => 'single_choice', 'question_text' => "Question text number {$i}", 'explanation' => "Because reason {$i}.", 'is_active' => true, 'status' => 'published']);
            foreach (['A', 'B', 'C', 'D'] as $n => $letter) {
                QuestionOption::create(['question_id' => $q->id, 'option_key' => $letter, 'option_text' => "Option {$letter} for {$i}", 'sort_order' => $n]);
            }
            QuestionAnswer::create(['question_id' => $q->id, 'answer_value' => 'B', 'sort_order' => 1]);
        }

        return $exam;
    }

    public function test_urls_are_the_hub_style(): void
    {
        $exam = $this->exam();

        $this->assertSame(url('/exams/dp-800'), $exam->url);
        $this->assertSame(url('/exams/dp-800/free-practice-questions'), $exam->questions_url);
        $this->assertSame(url('/exams/dp-800/plans'), $exam->plans_url);
    }

    public function test_sitemap_lists_every_page_a_hub_site_serves_and_nothing_that_redirects(): void
    {
        $exam = $this->exam();

        $xml = $this->get('/sitemap.xml')->assertOk()->getContent();

        foreach ([$exam->url, $exam->questions_url, $exam->plans_url] as $url) {
            $this->assertStringContainsString('<loc>' . $url . '</loc>', $xml);
        }
        $this->assertStringNotContainsString('<loc>' . url('/vendors') . '</loc>', $xml, '/vendors redirects on a one-vendor site');
        $this->assertStringNotContainsString('/guarantee', $xml, 'no guarantee page on a site without the guarantee');
        $this->assertStringContainsString('<loc>' . url('/vendors/microsoft') . '</loc>', $xml);
        $this->get('/guarantee')->assertNotFound();
    }

    public function test_public_pages_are_indexable_and_private_pages_are_noindex_follow(): void
    {
        \Illuminate\Support\Facades\DB::table('sites')->where('id', 3)->update(['robots_directive' => 'index, follow']);
        $exam = $this->exam();

        foreach ([url('/'), $exam->url, $exam->questions_url, $exam->plans_url, url('/vendors/microsoft'), url('/about'), url('/faq')] as $url) {
            $r = $this->get($url)->assertOk();
            $this->assertStringContainsString('<meta name="robots" content="index, follow', $r->getContent(), "meta robots on {$url}");
            $this->assertStringNotContainsString('noindex', (string) $r->headers->get('X-Robots-Tag'), "X-Robots-Tag on {$url}");
        }

        foreach (['/login', '/register', '/cart', '/search?q=az'] as $path) {
            $r = $this->get($path);
            $this->assertStringContainsString('<meta name="robots" content="noindex, follow', $r->getContent(), "meta robots on {$path}");
            $this->assertSame('noindex, follow', $r->headers->get('X-Robots-Tag'), "X-Robots-Tag on {$path}");
        }
    }

    public function test_each_page_has_its_own_title_h1_and_self_canonical(): void
    {
        $exam = $this->exam();

        $guide = $this->get($exam->url)->assertOk();
        $questions = $this->get($exam->questions_url)->assertOk();
        $plans = $this->get($exam->plans_url)->assertOk();

        $https = fn (string $u) => preg_replace('#^http:#', 'https:', $u);
        $guide->assertSee('<link rel="canonical" href="' . $https($exam->url) . '">', false);
        $questions->assertSee('<link rel="canonical" href="' . $https($exam->questions_url) . '">', false);
        $plans->assertSee('<link rel="canonical" href="' . $https($exam->plans_url) . '">', false);

        $titles = collect([$guide, $questions, $plans])->map(fn ($r) => preg_match('#<title>(.*?)</title>#s', $r->getContent(), $m) ? html_entity_decode($m[1]) : '')->all();
        $this->assertCount(3, array_unique($titles), 'three pages, three different titles');
        $this->assertStringContainsString('DP-800 Practice Questions', $titles[1]);
        $this->assertStringContainsString('DP-800 Practice Test', $titles[2]);

        $questions->assertSee('DP-800 practice questions with explanations');
        $plans->assertSee('DP-800 practice test plans');
    }

    public function test_all_free_questions_are_on_one_page_with_explanations_in_the_html(): void
    {
        $exam = $this->exam('DP-800', 7);

        $page = $this->get($exam->questions_url)->assertOk();
        foreach (range(1, 7) as $i) {
            $page->assertSee("Question text number {$i}")->assertSee("Because reason {$i}.", false);
        }
        $page->assertDontSee('?page=2', false);

        $this->get($exam->questions_url . '?page=2')->assertNotFound();
    }

    public function test_alternative_urls_301_to_the_canonical_one(): void
    {
        $exam = $this->exam();

        $this->get('/exams/DP-800')->assertStatus(301)->assertRedirect($exam->url);
        $this->get('/exams/microsoft/dp-800')->assertStatus(301)->assertRedirect($exam->url);
        $this->get('/exams/DP-800/plans')->assertStatus(301)->assertRedirect($exam->plans_url);
        $this->get('/exams')->assertStatus(301);
    }

    public function test_unknown_exam_and_other_vendors_are_404(): void
    {
        $this->exam();
        $cisco = Vendor::factory()->create(['slug' => 'cisco', 'name' => 'Cisco', 'is_active' => true]);
        Exam::factory()->create(['vendor_id' => $cisco->id, 'exam_code' => '200-301', 'slug' => '200-301', 'is_active' => true]);

        $this->get('/exams/no-such-exam')->assertNotFound();
        $this->get('/exams/200-301')->assertNotFound();
        $this->get('/exams/200-301/plans')->assertNotFound();
    }

    public function test_pages_never_use_dumps_wording_or_show_the_guarantee_card_and_carry_breadcrumbs(): void
    {
        $exam = $this->exam();

        foreach ([$exam->url, $exam->questions_url, $exam->plans_url] as $url) {
            $html = $this->get($url)->assertOk()->getContent();
            $this->assertDoesNotMatchRegularExpression('/dumps/i', $html);
            $this->assertStringNotContainsString('100% Pass Guarantee', $html);
            $this->assertStringContainsString('BreadcrumbList', $html);
            $this->assertStringContainsString('not affiliated with', $html);
        }
    }

    public function test_plans_page_shows_every_price_and_posts_to_the_existing_cart(): void
    {
        $exam = $this->exam();

        $html = $this->get($exam->plans_url)->assertOk()->getContent();

        $this->assertStringContainsString('$30', $html);
        $this->assertStringContainsString('$40', $html);
        $this->assertStringContainsString('$60', $html);
        $this->assertStringContainsString('action="' . route('cart.add') . '"', $html);
        foreach (['pdf', 'engine_single', 'combo'] as $type) {
            $this->assertStringContainsString('name="type" value="' . $type . '"', $html);
        }
        $this->assertStringContainsString('"@type":"Product"', str_replace(["\n", ' '], '', $html));
    }

    public function test_guide_page_marks_up_a_product_without_invented_ratings(): void
    {
        $exam = $this->exam();

        $html = $this->get($exam->url)->assertOk()->getContent();

        $this->assertStringContainsString('AggregateOffer', $html);
        $this->assertStringNotContainsString('aggregateRating', $html);
    }

    public function test_track_page_lists_the_exams_of_that_track(): void
    {
        $this->exam('DP-800');
        $this->exam('AZ-900', 1);

        $this->get('/tracks/data-ai')->assertOk()->assertSee('DP-800')->assertDontSee('AZ-900');
        $this->get('/tracks/azure-cloud')->assertOk()->assertSee('AZ-900');
        $this->get('/tracks/not-a-track')->assertNotFound();
    }

    public function test_redirect_rules_belong_to_the_site_that_created_them_and_do_not_leak(): void
    {
        $mine = Redirect::create(['old_url' => 'old-path', 'new_url' => 'new-path', 'status_code' => 301, 'is_active' => true]);
        $this->assertSame(3, (int) $mine->site_id);

        // A rule written for the core site must not rewrite this site's URLs.
        Redirect::query()->insert(['old_url' => 'exams/dp-800', 'new_url' => 'exams/microsoft/dp-800', 'status_code' => 301, 'is_active' => true, 'site_id' => 1, 'created_at' => now(), 'updated_at' => now()]);
        $exam = $this->exam();

        $this->get('/exams/dp-800')->assertOk();
    }
}
