<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomepageFaqTest extends TestCase
{
    use RefreshDatabase;

    public function test_homepage_lists_all_ten_faqs(): void
    {
        $response = $this->get(route('home'));
        $response->assertOk();

        foreach ([
            'What is ExamTopicsBase?',
            'Are free practice questions available before buying?',
            'Does ExamTopicsBase offer real exam questions?',
            'How does the web-based practice test engine work?',
            'What payment methods do you accept?',
            'Do you offer a 100% pass guarantee?',
            'What is your refund policy?',
            'How fast do I get access to my purchase?',
            'Can I access ExamTopicsBase on my phone or tablet?',
            'How often are exam topics and questions updated?',
        ] as $question) {
            $response->assertSee($question);
        }
    }

    public function test_homepage_emits_faqpage_structured_data_with_ten_questions(): void
    {
        $html = $this->get(route('home'))->getContent();

        $this->assertMatchesRegularExpression('/"@type":"FAQPage"/', $html);
        $this->assertSame(10, substr_count($html, '"@type":"Question"'));
    }

    public function test_homepage_does_not_use_the_word_dumps(): void
    {
        $html = $this->get(route('home'))->getContent();

        $this->assertStringNotContainsStringIgnoringCase('dump', $html);
    }
}
