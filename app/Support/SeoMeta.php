<?php

namespace App\Support;

use Illuminate\Support\Str;

/**
 * Titles and meta descriptions for the pages of a single-vendor exam site, built from data so every
 * page is unique. Each builder tries its variants from most to least descriptive and keeps the first that
 * fits Google's display limits (titles up to 60 characters, descriptions up to 158).
 */
class SeoMeta
{
    public const TITLE_MAX = 60;
    public const DESC_MAX = 158;

    /** First variant that fits; if none does, the shortest one cut at a word boundary. */
    public static function fit(array $variants, int $max): string
    {
        $variants = array_values(array_filter(array_map(fn ($v) => trim(preg_replace('/\s+/', ' ', (string) $v)), $variants)));
        foreach ($variants as $v) {
            if (mb_strlen($v) <= $max) {
                return $v;
            }
        }

        return Str::limit(end($variants) ?: '', $max, '');
    }

    public static function title(array $variants): string
    {
        return static::fit($variants, static::TITLE_MAX);
    }

    public static function description(array $variants): string
    {
        return static::fit($variants, static::DESC_MAX);
    }

    public static function home(string $vendor, string $brand, int $examCount): array
    {
        return [
            'title' => static::title([
                "{$vendor} Certification Practice Tests & Exam Guides | {$brand}",
                "{$vendor} Practice Tests & Exam Guides | {$brand}",
                "{$vendor} Practice Tests | {$brand}",
            ]),
            'description' => static::description([
                "Practise for {$examCount} {$vendor} certification exams with free questions and explanations, exam guides, PDF study guides and a timed test engine. Start free.",
                "Practise for {$examCount} {$vendor} exams with free questions and explanations, exam guides and a timed test engine. Start free.",
            ]),
        ];
    }

    /** "AZ-900 Exam Guide 2026: ..." (the year follows the calendar, so guides do not look stale) */
    public static function guide(string $code, string $name, string $brand): array
    {
        $year = now()->year;

        return [
            'title' => static::title([
                "{$code} Exam Guide {$year}: Skills, Format & Study Plan | {$brand}",
                "{$code} Exam Guide {$year}: Skills & Study Plan | {$brand}",
                "{$code} Exam Guide: Skills & Study Plan | {$brand}",
                "{$code} Exam Guide & Study Plan | {$brand}",
            ]),
            'description' => static::description([
                "{$code} {$name} exam guide: skills measured with weightings, a week-by-week study plan, passing score and FAQs. Then try free practice questions.",
                "{$code} exam guide: skills measured with weightings, a study plan, passing score and FAQs. Then try free practice questions.",
                "{$code} exam guide: skills measured, study plan and FAQs. Try free practice questions.",
            ]),
        ];
    }

    public static function questions(string $code, string $name, int $free, string $brand, int $page = 1): array
    {
        $suffix = $page > 1 ? ", Page {$page}" : '';

        return [
            'title' => static::title([
                "{$code} Practice Questions with Explanations (Free){$suffix} | {$brand}",
                "{$code} Practice Questions with Explanations{$suffix} | {$brand}",
                "Free {$code} Practice Questions + Answers{$suffix} | {$brand}",
                "Free {$code} Practice Questions{$suffix} | {$brand}",
            ]),
            'description' => static::description($free > 0 ? [
                "Answer {$free} free {$code} practice questions on {$name}. Check each answer, read the explanation and find your weak topics. No account needed.",
                "Answer {$free} free {$code} practice questions. Check each answer, read the explanation and find your weak topics. No account needed.",
                "{$free} free {$code} practice questions with explanations. No account needed.",
            ] : [
                "Free {$code} practice questions are being added. Read the {$code} exam guide and use Microsoft's free practice assessment meanwhile.",
            ]),
        ];
    }

    public static function plans(string $code, string $name, ?string $from, string $brand): array
    {
        $price = $from ? " from {$from}" : '';

        return [
            'title' => static::title([
                "{$code} Practice Test: PDF & Test Engine Plans | {$brand}",
                "{$code} Practice Test: PDF & Engine Plans | {$brand}",
                "{$code} Practice Test Plans | {$brand}",
            ]),
            'description' => static::description([
                "{$code} practice test plans{$price}: PDF study guide, timed online test engine or both, with explanations for every answer and 3 months of updates.",
                "{$code} practice test plans{$price}: PDF study guide, timed test engine or both, with explanations and 3 months of updates.",
                "{$code} practice test plans{$price}: PDF guide, timed test engine or both.",
            ]),
        ];
    }

    public static function track(string $track, int $count, array $codes, string $vendor, string $brand): array
    {
        $list = implode(', ', array_slice($codes, 0, 4));

        return [
            'title' => static::title([
                "{$track} Certification Exams: Practice Tests & Guides | {$brand}",
                "{$track} Exams: Practice Tests & Guides | {$brand}",
                "{$track} {$vendor} Exams | {$brand}",
            ]),
            'description' => static::description([
                "Practice tests and exam guides for {$count} {$vendor} {$track} exams ({$list}). See what each exam covers, try free questions and pick a study order.",
                "Practice tests and exam guides for {$count} {$vendor} {$track} exams ({$list}). Try free questions and pick a study order.",
                "Practice tests and exam guides for {$count} {$vendor} {$track} exams. Try free questions.",
            ]),
        ];
    }

    public static function vendor(string $vendor, int $count, string $brand): array
    {
        return [
            'title' => static::title([
                "{$vendor} Certification Exams: {$count} Practice Tests | {$brand}",
                "{$vendor} Exams: {$count} Practice Tests | {$brand}",
            ]),
            'description' => static::description([
                "Find your {$vendor} exam among {$count}. Compare skills, try free practice questions with explanations, and choose a PDF guide or timed test engine.",
                "Find your {$vendor} exam among {$count}. Try free practice questions with explanations and choose a PDF guide or test engine.",
            ]),
        ];
    }
}
