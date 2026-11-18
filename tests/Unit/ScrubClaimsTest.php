<?php

namespace Tests\Unit;

use App\Http\Middleware\ScrubClaims;
use PHPUnit\Framework\TestCase;

class ScrubClaimsTest extends TestCase
{
    public function test_marketing_phrases_are_rewritten(): void
    {
        $in = '<p>Real exam questions and actual exam dumps. Latest Dumps for AZ-900 dumps, SC-200 dumps and 300-435 dumps. Free braindumps. Practice under real exam conditions in the Actual Exam.</p>';
        $out = ScrubClaims::clean($in);

        $this->assertStringNotContainsStringIgnoringCase('dumps', $out);
        $this->assertStringNotContainsStringIgnoringCase('real exam', $out);
        $this->assertStringNotContainsStringIgnoringCase('actual exam', $out);
        $this->assertStringContainsString('AZ-900 practice questions', $out);
        $this->assertStringContainsString('300-435 practice questions', $out);
        $this->assertStringContainsString('Latest questions', $out);
        $this->assertStringContainsString('Exam questions', $out);
        $this->assertStringContainsString('under exam conditions', $out);
    }

    public function test_technical_terms_in_practice_questions_are_left_alone(): void
    {
        $in = '<p>Which tool analyses core dumps and database dumps? Take a memory dump, then review crash dumps.</p>';
        $this->assertSame($in, ScrubClaims::clean($in));
    }
}
