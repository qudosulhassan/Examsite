<?php

namespace App\Support;

/**
 * Keyword plan for one exam page: which phrases the page targets, grouped by search intent, and the title,
 * description and H1 built around them.
 *
 * Method (see docs/keyword-research-core.md): the phrase patterns come from Google autocomplete for every exam
 * (resources/seo-content/keywords/core-autocomplete.json); the page text is written around the strongest patterns.
 * There is no search-volume data here, only which phrases people really type.
 *
 * Wording rule: titles, descriptions and headings never use "dumps", "braindumps", "real/actual exam" or a pass-rate
 * promise. Those phrases are listed in the plan as `avoid` so the research is visible, but they are not targeted.
 */
class ExamKeywordPlan
{
    public const TITLE_MAX = 60;
    public const DESC_MAX = 158;
    public const H1_MAX = 80;

    /**
     * @param  array{code:string,name?:?string,vendor?:?string,slug?:?string}  $exam
     * @param  array<string>  $autocomplete  suggestions Google offered for this exam (lowercase)
     * @return array<string,mixed>
     */
    public static function forExam(array $exam, string $brand, int $questionCount = 0, array $autocomplete = []): array
    {
        $code = trim((string) ($exam['code'] ?? ''));
        $name = static::cleanName((string) ($exam['name'] ?? ''), (string) ($exam['vendor'] ?? ''), $code);
        $vendor = trim((string) ($exam['vendor'] ?? ''));
        $lower = mb_strtolower($code);

        $groups = [
            'primary'   => ["{$lower} practice test", "{$lower} practice questions", "{$lower} exam questions", "{$lower} practice exam"],
            'free'      => ["{$lower} free practice test", "{$lower} sample questions", "{$lower} practice test pdf"],
            'info'      => ["{$lower} study guide", "{$lower} exam objectives", "{$lower} passing score", "{$lower} exam cost", "{$lower} syllabus"],
            'long_tail' => array_values(array_filter([
                $name !== '' ? mb_strtolower("{$name} practice test") : null,
                $name !== '' && $vendor !== '' ? mb_strtolower("{$vendor} {$name} exam questions") : null,
                $name !== '' ? mb_strtolower("{$code} {$name}") : null,
            ])),
            // Phrases people also type, which this site does not target (policy and copyright risk).
            'avoid'     => ["{$lower} dumps", "{$lower} braindumps", "{$lower} actual exam questions", "{$lower} exam topics"],
        ];

        $confirmed = array_values(array_intersect(
            array_merge($groups['primary'], $groups['free'], $groups['info'], $groups['long_tail'], $groups['avoid']),
            array_map('mb_strtolower', $autocomplete)
        ));

        return [
            'code'      => $code,
            'name'      => $name,
            'title'     => static::title($code, $name, $brand),
            'description' => static::description($code, $name, $questionCount),
            'h1'        => static::h1($code, $name, $vendor),
            'primary'   => $groups['primary'][0],
            'groups'    => $groups,
            'confirmed' => $confirmed,
        ];
    }

    /** @return array{title:string,description:string,h1:string} for a vendor page, e.g. "Cisco". */
    public static function forVendor(string $vendor, string $brand, int $examCount = 0): array
    {
        $suffix = " | {$brand}";
        $titles = ["{$vendor} Certification Practice Tests & Questions{$suffix}", "{$vendor} Certification Practice Tests{$suffix}", "{$vendor} Practice Tests{$suffix}"];
        $title = mb_substr($titles[count($titles) - 1], 0, self::TITLE_MAX);
        foreach ($titles as $t) {
            if (mb_strlen($t) <= self::TITLE_MAX) {
                $title = $t;
                break;
            }
        }
        $exams = $examCount >= 10 ? (floor($examCount / 10) * 10) . '+ ' : '';
        $desc = "{$vendor} certification practice tests and exam questions for {$exams}{$vendor} exams. Free samples, study guides and a timed test engine.";
        if (mb_strlen($desc) > self::DESC_MAX) {
            $desc = "{$vendor} certification practice tests and exam questions. Free samples, study guides and a timed test engine.";
        }

        return ['title' => $title, 'description' => $desc, 'h1' => "{$vendor} Certification Practice Tests & Exam Questions"];
    }

    /** @param array<string> $vendors the biggest vendors by exam count */
    public static function forHome(string $brand, int $examCount, array $vendors): array
    {
        $exams = $examCount >= 100 ? (floor($examCount / 100) * 100) . '+' : (string) $examCount;
        $names = implode(', ', array_slice($vendors, 0, 3));
        $desc = "Practice tests and exam questions for {$exams} IT certification exams from {$names} and more. Free samples, study guides and a timed test engine.";
        if (mb_strlen($desc) > self::DESC_MAX) {
            $desc = "Practice tests and exam questions for {$exams} IT certification exams. Free samples, study guides and a timed test engine.";
        }

        return ['title' => "IT Certification Practice Tests & Questions | {$brand}", 'description' => $desc];
    }

    /** First candidate that fits in 60 characters; the exam code always comes first. */
    public static function title(string $code, string $name, string $brand): string
    {
        $suffix = " | {$brand}";
        $candidates = [];
        if ($name !== '') {
            $candidates[] = "{$code} {$name} Practice Test & Questions{$suffix}";
        }
        $candidates[] = "{$code} Practice Test & Exam Questions{$suffix}";
        $candidates[] = "{$code} Practice Questions & Test{$suffix}";
        $candidates[] = "{$code} Practice Test{$suffix}";

        foreach ($candidates as $c) {
            if (mb_strlen($c) <= self::TITLE_MAX) {
                return $c;
            }
        }

        return mb_substr($candidates[count($candidates) - 1], 0, self::TITLE_MAX);
    }

    public static function description(string $code, string $name, int $questionCount = 0): string
    {
        $label = $name !== '' ? "{$code} {$name}" : $code;
        $count =$questionCount >= 10 ? ' with ' . (floor($questionCount / 10) * 10) . '+ practice questions and answers' : ' with questions and answers';

        $variants = [
            "{$label} practice test{$count}. Try a free sample, review the exam objectives and practise in a timed test engine.",
            "{$label} practice test{$count}. Free sample, study guide and timed test engine.",
            "{$code} practice test{$count}. Free sample, exam objectives and a timed test engine.",
            "{$code} practice test and questions. Free sample, study guide and timed test engine.",
        ];
        foreach ($variants as $v) {
            if (mb_strlen($v) <= self::DESC_MAX) {
                return $v;
            }
        }

        return rtrim(mb_substr($variants[count($variants) - 1], 0, self::DESC_MAX - 1), ' ,.') . '.';
    }

    public static function h1(string $code, string $name, string $vendor): string
    {
        $core = 'Practice Test & Exam Questions';
        // the vendor is left out when the exam name already starts with it ("Microsoft Azure Fundamentals")
        $vendor = ($vendor !== '' && stripos($name, $vendor) === 0) ? '' : $vendor;
        $full = trim(($vendor !== '' ? "{$vendor} " : '') . "{$code} " . ($name !== '' ? "{$name} " : '') . $core);
        if (mb_strlen($full) <= self::H1_MAX) {
            return $full;
        }
        $mid = trim(($vendor !== '' ? "{$vendor} " : '') . "{$code} {$core}");

        return mb_strlen($mid) <= self::H1_MAX ? $mid : "{$code} {$core}";
    }

    /** Drops a repeated exam code from the front of an exam name ("AZ-900: Azure Fundamentals" becomes "Azure Fundamentals"). */
    public static function cleanName(string $name, string $vendor, string $code): string
    {
        $name = trim(preg_replace('/\s+/', ' ', strip_tags($name)));
        if ($code !== '' && stripos($name, $code) === 0) {
            $name = ltrim(substr($name, strlen($code)), " :-–—");
        }
        return trim($name);
    }
}
