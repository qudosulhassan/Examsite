<?php

namespace App\Support;

use App\Models\Exam;
use Illuminate\Support\Arr;

/**
 * Builds the body of an exam guide page from Microsoft's official study guide for that exam.
 *
 * The study guides are read from Microsoft Learn into resources/seo-content/learn/{slug}.json
 * (domains, weightings, skills, audience, the date the skills outline was last updated, and any
 * retirement notice). Everything shown on the page is either quoted from that data or derived from it
 * (counts, ranks, study-plan weeks), so nothing about an exam is invented. An exam with no guide on
 * Microsoft Learn (retired long ago, or not an exam any more) returns null and gets a minimal page.
 */
class ExamGuideContent
{
    /** Microsoft's published pass mark for its exams. */
    public const PASSING = '700 on a scale of 1 to 1,000';

    /** @var array<string, array|null> */
    protected static array $cache = [];

    public static function load(string $slug): ?array
    {
        $slug = strtolower($slug);

        if (!array_key_exists($slug, static::$cache)) {
            $path = resource_path("seo-content/learn/{$slug}.json");
            static::$cache[$slug] = is_file($path) ? json_decode((string) file_get_contents($path), true) : null;
        }

        return static::$cache[$slug];
    }

    /**
     * Whether search engines should see this exam's pages: it needs a current Microsoft Learn study guide and must not
     * be retired. Retired or unlisted exams keep their pages for existing visitors but stay out of the index and sitemap.
     */
    public static function isIndexable(string $slug): bool
    {
        $data = static::load($slug);

        return $data !== null && !empty($data['domains']) && ($data['retire']['status'] ?? null) !== 'retired';
    }

    /**
     * @param  int  $freeCount  free questions published for this exam
     * @param  int  $bankCount  questions in the whole bank
     * @return array|null  null when Microsoft Learn has no study guide for the exam
     */
    public static function for(Exam $exam, int $freeCount = 0, int $bankCount = 0): ?array
    {
        $data = static::load((string) $exam->slug);
        if (!$data || empty($data['domains'])) {
            return null;
        }

        $code = (string) $exam->exam_code;
        $official = $data['official'] ?: trim((string) $exam->exam_name);
        $domains = array_map(fn ($d) => static::domain($d, $code), $data['domains']);

        // rank by the top of each weight range so the heaviest domains can be called out
        $maxes = array_map(fn ($d) => $d['weightMax'], $domains);
        $top = max($maxes);
        $low = min($maxes);
        foreach ($domains as $i => &$d) {
            if (count($domains) > 2 && $d['weightMax'] === $top) {
                $d['explanation'] .= ' It is one of the heaviest parts of the outline, so it deserves the largest share of your study time.';
            } elseif (count($domains) > 2 && $d['weightMax'] === $low) {
                $d['explanation'] .= ' It carries the smallest weight, but its questions still count towards your score, so do not skip it.';
            }
        }
        unset($d);

        $skillTotal = array_sum(array_column($domains, 'skillCount'));
        $level = stripos($official, 'Fundamentals') !== false ? 'Fundamentals' : null;
        $plan = static::plan($domains, $skillTotal, $level === 'Fundamentals', $code);
        $weeks = count($plan);

        $audience = array_values(array_filter($data['audience'] ?? [], fn ($p) => mb_strlen($p) > 30));

        return [
            'code'        => $code,
            'official'    => $official,
            'level'       => $level,
            'guideUrl'    => $data['url'] ?? null,
            'asOf'        => $data['asOf'] ?? null,
            'retire'      => $data['retire'] ?? null,
            'audience'    => $audience,
            'domains'     => $domains,
            'skillTotal'  => $skillTotal,
            'plan'        => $plan,
            'weeks'       => $weeks,
            'passing'     => static::PASSING,
            'faqs'        => static::faqs($code, $official, $domains, $data, $weeks, $freeCount, $bankCount),
        ];
    }

    protected static function domain(array $d, string $code): array
    {
        $sections = $d['sections'] ?? [];
        $titles = array_column($sections, 'title');
        $skillCount = array_sum(array_map(fn ($s) => count($s['skills']), $sections));
        preg_match_all('/\d+/', (string) $d['weight'], $m);
        $weightMax = (int) (end($m[0]) ?: 0);
        $weightMin = (int) (reset($m[0]) ?: 0);

        $sentences = ["{$d['name']} makes up {$d['weight']} of the {$code} skills outline."];

        if ($titles) {
            $shown = array_slice($titles, 0, 4);
            $more = count($titles) - count($shown);
            $sentences[] = 'Microsoft splits it into ' . (count($titles) === 1 ? 'one area' : count($titles) . ' areas') . ': '
                . static::joinList($shown) . ($more > 0 ? ", and {$more} more" : '') . '.';
        }

        // three example tasks, one from each of the first sections, quoted from the official list
        $examples = [];
        foreach (array_slice($sections, 0, 3) as $s) {
            $skill = Arr::first($s['skills']);
            if ($skill) {
                $examples[] = '“' . rtrim($skill, '.') . '”';
            }
        }
        if ($examples) {
            $sentences[] = 'Tasks Microsoft lists here include ' . static::joinList($examples) . '.';
        }

        return [
            'name'        => $d['name'],
            'weight'      => $d['weight'],
            'weightMax'   => $weightMax,
            'weightMid'   => ($weightMin + $weightMax) / 2,
            'sections'    => $sections,
            'skillCount'  => $skillCount,
            'explanation' => implode(' ', $sentences),
        ];
    }

    /**
     * A week-by-week plan: heavier domains first and in proportion, ending with timed practice.
     *
     * @return array<int, array{week: int, title: string, focus: array<string>, text: string}>
     */
    protected static function plan(array $domains, int $skillTotal, bool $fundamentals, string $code): array
    {
        $weeks = match (true) {
            $fundamentals || (count($domains) <= 3 && $skillTotal < 70) => 2,
            count($domains) >= 6 || $skillTotal > 110 => 4,
            default => 3,
        };

        $studyWeeks = $weeks - 1;
        $sorted = $domains;
        usort($sorted, fn ($a, $b) => $b['weightMid'] <=> $a['weightMid']);

        // greedy: put the next-heaviest domain into the week with the smallest total so far
        $buckets = array_fill(0, $studyWeeks, ['load' => 0.0, 'domains' => []]);
        foreach ($sorted as $d) {
            $i = array_search(min(array_column($buckets, 'load')), array_column($buckets, 'load'), true);
            $buckets[$i]['load'] += $d['weightMid'];
            $buckets[$i]['domains'][] = $d;
        }

        $plan = [];
        foreach ($buckets as $i => $b) {
            if (!$b['domains']) {
                continue;
            }
            $names = array_column($b['domains'], 'name');
            $first = Arr::first($b['domains']);
            $sectionNames = array_slice(array_column($first['sections'], 'title'), 0, 3);
            $plan[] = [
                'week'  => count($plan) + 1,
                'title' => static::joinList($names),
                'focus' => $names,
                'text'  => 'Work through the Microsoft Learn material for ' . static::joinList($names)
                    . ($sectionNames ? ', starting with ' . static::joinList($sectionNames) : '')
                    . '. Try each task hands-on where you can, then answer practice questions on these topics and read every explanation.',
            ];
        }

        $plan[] = [
            'week'  => count($plan) + 1,
            'title' => 'Practise under exam conditions',
            'focus' => [],
            'text'  => "Take timed {$code} practice sessions, mark the questions you guessed, and go back to the domains where you lost points. Repeat until you pass your practice sessions comfortably, then book the exam.",
        ];

        return $plan;
    }

    protected static function faqs(string $code, string $official, array $domains, array $data, int $weeks, int $freeCount, int $bankCount): array
    {
        $domainList = implode('; ', array_map(fn ($d) => "{$d['name']} ({$d['weight']})", $domains));
        $audience = trim((string) Arr::first($data['audience'] ?? []));
        $firstSentence = $audience !== '' ? trim(strtok($audience, '.')) . '.' : null;

        $faqs = [
            ['q' => "What is the {$code} exam?",
             'a' => static::describe($code, $official) . ($firstSentence ? " Microsoft describes the candidate this way: “{$firstSentence}”" : '')],
            ['q' => "What topics does {$code} cover and how are they weighted?",
             'a' => 'Microsoft groups the skills into ' . count($domains) . ' domains: ' . $domainList . '.'
                 . (!empty($data['asOf']) ? " The skills outline was last updated on {$data['asOf']}." : '')],
            ['q' => "What is the passing score for {$code}?",
             'a' => 'Microsoft sets the passing score at ' . static::PASSING . '. It is a scaled score, not a percentage of questions answered correctly, so you cannot work out your result by simply counting right answers.'],
            ['q' => "How long should I study for {$code}?",
             'a' => "Plan on about {$weeks} weeks of steady study for {$code}, then adjust for your experience. Someone who already works with these tools daily may need less time; someone new to them will need more. The study plan on this page splits the weeks by domain weighting."],
        ];

        if (!empty($data['retire'])) {
            $r = $data['retire'];
            $faqs[] = ['q' => "Is {$code} being retired?",
                       'a' => $r['status'] === 'retired'
                           ? "Microsoft retired {$code} on {$r['date']}. Check the official exam page for the replacement exam before you start studying."
                           : "Microsoft has announced that {$code} retires on {$r['date']}. If you plan to take it, book it before then and check the official exam page for its replacement."];
        } else {
            $faqs[] = ['q' => "Is {$code} still current?",
                       'a' => 'Microsoft publishes the skills outline for each exam on Microsoft Learn and updates it from time to time.'
                           . (!empty($data['asOf']) ? " The outline for {$code} was last updated on {$data['asOf']}." : '')
                           . ' Always read the current study guide before you book.'];
        }

        $faqs[] = ['q' => "How can I practise {$code} questions for free?",
                   'a' => $freeCount > 0
                       ? "Answer the {$freeCount} free {$code} practice questions on our free practice questions page. You can check each answer and read the explanation without creating an account." . ($bankCount > $freeCount ? " The full bank has {$bankCount} questions." : '')
                       : "Free {$code} practice questions are being added. In the meantime, Microsoft offers free practice assessments for many exams on Microsoft Learn."];

        return $faqs;
    }

    /** "AZ-900 is the Microsoft Azure Fundamentals exam from Microsoft." without repeating the vendor name. */
    public static function describe(string $code, string $official, string $vendor = 'Microsoft'): string
    {
        return "{$code} is the {$official} exam from {$vendor}.";
    }

    protected static function joinList(array $items): string
    {
        $items = array_values($items);

        return match (count($items)) {
            0 => '',
            1 => $items[0],
            2 => $items[0] . ' and ' . $items[1],
            default => implode(', ', array_slice($items, 0, -1)) . ' and ' . end($items),
        };
    }
}
