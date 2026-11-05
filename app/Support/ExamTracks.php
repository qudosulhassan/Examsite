<?php

namespace App\Support;

use Illuminate\Support\Collection;

/**
 * Groups a vendor's exams into learning "tracks" by exam-code prefix (AZ-900 -> Azure, SC-300 ->
 * Security, ...). Used by single-vendor sites for their homepage and navigation. New exams join
 * the right track automatically; anything with an unknown prefix lands in "More exams".
 */
class ExamTracks
{
    /**
     * @return array<string, array{title: string, blurb: string, prefixes: array<string>, icon: string}>
     */
    public static function definitions(string $vendorSlug): array
    {
        return match ($vendorSlug) {
            'microsoft' => [
                'azure' => [
                    'slug' => 'azure-cloud',
                    'title' => 'Azure & Cloud',
                    'blurb' => 'Fundamentals, administration, development and architecture on Azure.',
                    'prefixes' => ['AZ'],
                    'icon' => 'cloud',
                ],
                'security' => [
                    'slug' => 'security-identity',
                    'title' => 'Security & Identity',
                    'blurb' => 'Security operations, identity and access, compliance.',
                    'prefixes' => ['SC'],
                    'icon' => 'shield',
                ],
                'm365' => [
                    'slug' => 'microsoft-365',
                    'title' => 'Microsoft 365',
                    'blurb' => 'Teams, endpoints, messaging and productivity administration.',
                    'prefixes' => ['MS', 'MD'],
                    'icon' => 'layers',
                ],
                'power' => [
                    'slug' => 'power-platform',
                    'title' => 'Power Platform',
                    'blurb' => 'Power Apps, Power Automate, Power BI and functional consulting.',
                    'prefixes' => ['PL'],
                    'icon' => 'bolt',
                ],
                'dynamics' => [
                    'slug' => 'dynamics-365',
                    'title' => 'Dynamics 365',
                    'blurb' => 'Sales, customer service, finance and supply chain.',
                    'prefixes' => ['MB'],
                    'icon' => 'chart',
                ],
                'data-ai' => [
                    'slug' => 'data-ai',
                    'title' => 'Data & AI',
                    'blurb' => 'Data engineering, analytics and applied AI.',
                    'prefixes' => ['DP', 'AI'],
                    'icon' => 'spark',
                ],
            ],
            default => [],
        };
    }

    /**
     * The hand-written introduction (three paragraphs) for a track, by its slug. Empty when there is none.
     *
     * @return array<int, string>
     */
    public static function intro(string $slug): array
    {
        static $all = null;
        $all ??= json_decode((string) @file_get_contents(resource_path('seo-content/track-intros.json')), true) ?: [];

        return $all[$slug] ?? [];
    }

    /**
     * The track an exam belongs to, by its code prefix: ['key', 'slug', 'title'] or null.
     */
    public static function forExam(string $examCode, string $vendorSlug): ?array
    {
        $prefix = strtoupper(strtok($examCode, '-') ?: '');

        foreach (static::definitions($vendorSlug) as $key => $def) {
            if (in_array($prefix, $def['prefixes'], true)) {
                return ['key' => $key, 'slug' => $def['slug'], 'title' => $def['title']];
            }
        }

        return null;
    }

    /**
     * @param  Collection<int, \App\Models\Exam>  $exams
     * @return array<int, array{key: string, title: string, blurb: string, icon: string, prefixes: array<string>, exams: Collection}>
     *         Only tracks that actually have exams, biggest first; unmatched exams go into "More exams".
     */
    public static function group(Collection $exams, string $vendorSlug): array
    {
        $definitions = static::definitions($vendorSlug);
        $buckets = [];
        $assigned = [];

        foreach ($definitions as $key => $def) {
            $matching = $exams->filter(function ($exam) use ($def) {
                $prefix = strtoupper(strtok((string) $exam->exam_code, '-') ?: '');

                return in_array($prefix, $def['prefixes'], true);
            })->values();

            if ($matching->isNotEmpty()) {
                $buckets[] = ['key' => $key] + $def + ['exams' => $matching];
                $assigned = array_merge($assigned, $matching->pluck('id')->all());
            }
        }

        $rest = $exams->reject(fn ($exam) => in_array($exam->id, $assigned, true))->values();
        if ($rest->isNotEmpty()) {
            $buckets[] = [
                'key' => 'more',
                'title' => 'More exams',
                'blurb' => 'Other certification exams in the catalog.',
                'prefixes' => [],
                'slug' => 'more-exams',
                'icon' => 'grid',
                'exams' => $rest,
            ];
        }

        usort($buckets, fn ($a, $b) => $b['exams']->count() <=> $a['exams']->count());

        return $buckets;
    }
}
