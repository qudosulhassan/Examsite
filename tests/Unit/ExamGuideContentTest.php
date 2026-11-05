<?php

namespace Tests\Unit;

use App\Models\Exam;
use App\Support\ExamGuideContent;
use App\Support\SeoMeta;
use Tests\TestCase;

class ExamGuideContentTest extends TestCase
{
    private function exam(string $code): Exam
    {
        return new Exam(['exam_code' => strtoupper($code), 'slug' => strtolower($code), 'exam_name' => 'Whatever the database says']);
    }

    public function test_guide_is_built_from_the_official_study_guide(): void
    {
        $g = ExamGuideContent::for($this->exam('az-900'), 10, 295);

        $this->assertSame('Microsoft Azure Fundamentals', $g['official']);
        $this->assertSame('Fundamentals', $g['level']);
        $this->assertCount(3, $g['domains']);
        $this->assertSame('Describe cloud concepts', $g['domains'][0]['name']);
        $this->assertSame('25–30%', $g['domains'][0]['weight']);
        $this->assertStringContainsString('25–30% of the AZ-900 skills outline', $g['domains'][0]['explanation']);
        $this->assertSame(2, $g['weeks']);
        $this->assertSame($g['weeks'], count($g['plan']));
        $this->assertStringContainsString('timed', $g['plan'][array_key_last($g['plan'])]['text']);
        $this->assertCount(6, $g['faqs']);
        $this->assertStringContainsString('700 on a scale of 1 to 1,000', $g['faqs'][1]['a']);
        $this->assertStringContainsString('Describe Azure architecture and services', $g['faqs'][2]['q'] . $g['faqs'][2]['a']);
    }

    public function test_every_domain_of_the_outline_appears_in_the_study_plan(): void
    {
        $g = ExamGuideContent::for($this->exam('az-104'), 0, 0);

        $planned = collect($g['plan'])->pluck('focus')->flatten()->all();
        foreach ($g['domains'] as $d) {
            $this->assertContains($d['name'], $planned);
        }
        $this->assertGreaterThanOrEqual(3, $g['weeks']);
    }

    public function test_retiring_and_retired_exams_say_so(): void
    {
        $retiring = ExamGuideContent::for($this->exam('ms-102'), 0, 0);
        $this->assertSame('retiring', $retiring['retire']['status']);
        $this->assertStringContainsString('retires on', implode(' ', array_column($retiring['faqs'], 'a')));

        $retired = ExamGuideContent::for($this->exam('pl-400'), 0, 0);
        $this->assertSame('retired', $retired['retire']['status']);
    }

    public function test_only_current_exams_with_an_official_guide_are_indexable(): void
    {
        $this->assertTrue(ExamGuideContent::isIndexable('az-900'));
        $this->assertTrue(ExamGuideContent::isIndexable('ms-102'), 'retiring exams are still current');
        $this->assertFalse(ExamGuideContent::isIndexable('pl-400'), 'retired');
        $this->assertFalse(ExamGuideContent::isIndexable('az-303'), 'no study guide on Microsoft Learn');
        $this->assertNull(ExamGuideContent::for($this->exam('az-303'), 0, 0));
    }

    public function test_titles_and_descriptions_respect_the_display_limits(): void
    {
        foreach ([['AZ-900', 'Microsoft Azure Fundamentals'], ['MB-330', 'Microsoft Dynamics 365 Supply Chain Management Functional Consultant'], ['SC-500', 'Implementing End-to-End Security Controls for Cloud and AI Workloads']] as [$code, $name]) {
            foreach ([SeoMeta::guide($code, $name, 'AzureExamsHub'), SeoMeta::questions($code, $name, 10, 'AzureExamsHub'), SeoMeta::plans($code, $name, '$29', 'AzureExamsHub')] as $m) {
                $this->assertLessThanOrEqual(60, mb_strlen($m['title']), $m['title']);
                $this->assertStringEndsWith('| AzureExamsHub', $m['title']);
                $this->assertLessThanOrEqual(158, mb_strlen($m['description']), $m['description']);
                $this->assertStringContainsString($code, $m['title']);
            }
        }

        $this->assertNotSame(
            SeoMeta::guide('AZ-900', 'Microsoft Azure Fundamentals', 'AzureExamsHub')['title'],
            SeoMeta::plans('AZ-900', 'Microsoft Azure Fundamentals', null, 'AzureExamsHub')['title']
        );
    }

    /** Words of an answer with the exam code, digits and punctuation removed, as 3-word shingles. */
    private function shingles(string $text, string $code): array
    {
        $text = mb_strtolower(str_ireplace($code, '', $text));
        $words = preg_split('/\W+/u', preg_replace('/\d+/', '', $text), -1, PREG_SPLIT_NO_EMPTY);
        $out = [];
        for ($i = 0; $i + 2 < count($words); $i++) {
            $out[$words[$i] . ' ' . $words[$i + 1] . ' ' . $words[$i + 2]] = true;
        }

        return array_keys($out);
    }

    public function test_faqs_are_different_on_every_exam_page(): void
    {
        $questions = [];
        $answers = [];
        foreach (glob(resource_path('seo-content/learn/*.json')) as $file) {
            $slug = basename($file, '.json');
            $code = strtoupper($slug);
            $g = ExamGuideContent::for($this->exam($slug), 10, 100);
            $this->assertCount(6, $g['faqs'], $code);
            foreach ($g['faqs'] as $i => $faq) {
                $q = mb_strtolower($faq['q']);
                $this->assertArrayNotHasKey($q, $questions, "{$code} repeats the question of " . ($questions[$q] ?? '?'));
                $questions[$q] = $code;
                $answers[$code][$i] = $this->shingles($faq['a'], $code);
            }
        }

        // Compare answers slot by slot across exams: after removing the exam code and numbers they must not be near-copies.
        $codes = array_keys($answers);
        $worst = 0.0;
        $worstPair = '';
        foreach ($codes as $x => $a) {
            foreach (array_slice($codes, $x + 1) as $b) {
                foreach ([0, 2, 3, 4] as $slot) {   // the slots that are free text rather than the weight list
                    $sa = $answers[$a][$slot];
                    $sb = $answers[$b][$slot];
                    $union = count(array_unique(array_merge($sa, $sb)));
                    $sim = $union ? count(array_intersect($sa, $sb)) / $union : 0;
                    if ($sim > $worst) {
                        $worst = $sim;
                        $worstPair = "{$a} vs {$b} (slot {$slot})";
                    }
                }
            }
        }
        $this->assertLessThan(0.6, $worst, "Answers too similar: {$worstPair} = " . round($worst, 2));
    }

    public function test_every_fixed_page_has_a_unique_title_and_description(): void
    {
        $titles = $descs = [];
        foreach (['about', 'contact', 'faq', 'privacy-policy', 'dmca', 'test-engine', 'certifications', 'blog', 'free-demo'] as $p) {
            $m = SeoMeta::staticPage($p, 'AzureExamsHub');
            $this->assertLessThanOrEqual(60, mb_strlen($m['title']), $m['title']);
            $this->assertLessThanOrEqual(158, mb_strlen($m['description']), $m['description']);
            $titles[] = $m['title'];
            $descs[] = $m['description'];
        }
        $this->assertSame($titles, array_values(array_unique($titles)));
        $this->assertSame($descs, array_values(array_unique($descs)));
        $this->assertNull(SeoMeta::staticPage('exams/az-900', 'AzureExamsHub'));
    }
}
