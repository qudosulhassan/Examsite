<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class BlogTitlesFileTest extends TestCase
{
    public function test_every_reviewed_blog_title_fits_and_has_no_risky_wording(): void
    {
        $map = json_decode((string) file_get_contents(__DIR__ . '/../../resources/seo-content/blog-titles-core.json'), true);
        $this->assertCount(30, $map);
        foreach ($map as $slug => $title) {
            $final = mb_strlen("{$title} | ExamTopicsBase") <= 60 ? "{$title} | ExamTopicsBase" : $title;
            $this->assertLessThanOrEqual(60, mb_strlen($final), $slug);
            $this->assertGreaterThanOrEqual(30, mb_strlen($title), $slug);
            $this->assertDoesNotMatchRegularExpression('/dumps|real exam|actual exam|pass rate|guarantee/i', $title, $slug);
        }
        $this->assertCount(count($map), array_unique($map), 'titles are unique');
    }
}
