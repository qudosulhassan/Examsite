<?php

namespace Tests\Unit;

use App\Support\ExamGuideContent;
use Carbon\Carbon;
use Tests\TestCase;

class ExamRetirementTest extends TestCase
{
    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_a_retiring_exam_stays_indexable_until_its_retirement_date_then_drops_out(): void
    {
        $this->assertSame('retiring', ExamGuideContent::load('ms-102')['retire']['status'] ?? null);

        Carbon::setTestNow(Carbon::parse('2026-11-20 09:00', 'UTC'));
        $this->assertTrue(ExamGuideContent::isIndexable('ms-102'));
    }

    public function test_after_the_retirement_date_the_exam_is_no_longer_indexable(): void
    {
        $reflection = new \ReflectionProperty(ExamGuideContent::class, 'cache');
        $reflection->setAccessible(true);
        $reflection->setValue(null, []);

        Carbon::setTestNow(Carbon::parse('2026-12-01 09:00', 'UTC'));
        $this->assertFalse(ExamGuideContent::isIndexable('ms-102'));

        $reflection->setValue(null, []);
    }
}
