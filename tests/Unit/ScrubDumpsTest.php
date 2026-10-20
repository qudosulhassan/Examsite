<?php

namespace Tests\Unit;

use App\Services\SiteContext;
use Tests\TestCase;

class ScrubDumpsTest extends TestCase
{
    public function test_scrub_replaces_the_word_dumps_only(): void
    {
        $this->assertSame('Real exam questions and practice questions help.', SiteContext::scrubDumps('Real exam dumps and dumps help.'));
        $this->assertSame('Free exam questions', SiteContext::scrubDumps('Free exam Dumps'));
        $this->assertSame('var_dump and dumpster stay', SiteContext::scrubDumps('var_dump and dumpster stay'));
    }

    public function test_rebrand_applies_the_scrub_only_when_the_site_asks_for_it(): void
    {
        config(['site.id' => 3, 'app.name' => 'StackCert', 'site.avoid_dumps' => false]);
        $this->assertSame('StackCert has exam dumps', SiteContext::rebrandTemplate('ExamTopicsBase has exam dumps'));

        config(['site.avoid_dumps' => true]);
        $this->assertSame('StackCert has exam questions', SiteContext::rebrandTemplate('ExamTopicsBase has exam dumps'));

        config(['site.id' => 1]);
        $this->assertSame('ExamTopicsBase has exam dumps', SiteContext::rebrandTemplate('ExamTopicsBase has exam dumps'));
    }
}
