<?php

namespace App\Support;

use App\Models\BlogPost;
use App\Models\Exam;
use Illuminate\Support\Collection;

/**
 * Related exams and articles for an exam page. The order is fixed (the old block picked exams at random on every
 * request, so the links changed each time a crawler visited). Exams come first when they are in the same family:
 * same code prefix, the usual next/previous exam, or a similar name.
 */
class RelatedExams
{
    private const STOP = ['exam', 'certified', 'certification', 'associate', 'professional', 'expert', 'specialty', 'fundamentals', 'solutions', 'implementing', 'designing', 'administering', 'managing', 'with', 'using', 'and', 'the', 'for', 'microsoft', 'cisco', 'amazon', 'google', 'oracle', 'cloud', 'certificate'];

    /** @return Collection<int, Exam> */
    public static function exams(Exam $exam, int $limit = 6): Collection
    {
        $prefix = strtoupper(strtok((string) $exam->exam_code, '-') ?: '');
        $path = ExamGuideContent::path((string) $exam->slug);
        $family = array_merge($path['previous'], $path['next']);
        $mine = static::tokens((string) $exam->exam_name);

        $candidates = Exam::where('is_active', true)
            ->where('vendor_id', $exam->vendor_id)
            ->where('id', '!=', $exam->id)
            ->with('vendor')
            ->withCount('questions')
            ->get();

        return $candidates->map(function (Exam $c) use ($prefix, $family, $mine) {
            $score = 0.0;
            if ($prefix !== '' && strtoupper(strtok((string) $c->exam_code, '-') ?: '') === $prefix) {
                $score += 4;
            }
            if (in_array((string) $c->slug, $family, true)) {
                $score += 6;
            }
            $score += min(6, 1.5 * count(array_intersect($mine, static::tokens((string) $c->exam_name))));
            $score += min(2, $c->questions_count / 100);

            return [$score, $c];
        })->sort(function ($a, $b) {
            return [$b[0], $a[1]->exam_code] <=> [$a[0], $b[1]->exam_code];
        })->take($limit)->map(fn ($pair) => $pair[1])->values();
    }

    /** @return Collection<int, BlogPost> up to $limit published posts, most relevant first */
    public static function posts(Exam $exam, int $limit = 3): Collection
    {
        $code = strtolower(trim((string) $exam->exam_code));
        $base = fn () => BlogPost::with('category', 'user')->where('status', 'published');

        $posts = $base()->where('related_exam_id', $exam->id)->latest('published_at')->limit($limit)->get();

        if ($posts->count() < $limit && $code !== '') {
            $more = $base()->whereNotIn('id', $posts->pluck('id'))
                ->where(function ($q) use ($code) {
                    $q->where('slug', 'like', '%' . $code . '%')->orWhere('title', 'like', '%' . $code . '%');
                })->latest('published_at')->limit($limit - $posts->count())->get();
            $posts = $posts->concat($more);
        }

        if ($posts->count() < $limit && $exam->vendor) {
            $vendor = strtolower((string) $exam->vendor->name);
            $more = $base()->whereNotIn('id', $posts->pluck('id'))
                ->where(function ($q) use ($vendor) {
                    $q->where('slug', 'like', '%' . $vendor . '%')->orWhere('title', 'like', '%' . $vendor . '%');
                })->latest('published_at')->limit($limit - $posts->count())->get();
            $posts = $posts->concat($more);
        }

        return $posts->values();
    }

    /** @return array<string> */
    private static function tokens(string $name): array
    {
        $words = preg_split('/[^a-z0-9]+/', strtolower($name)) ?: [];

        return array_values(array_unique(array_filter($words, fn ($w) => strlen($w) > 3 && !in_array($w, self::STOP, true))));
    }
}
