<?php

namespace App\Support;

use App\Models\Exam;

/**
 * Questions and answers for an exam page that has no FAQs of its own. Every answer is built from facts the site
 * actually has (the exam's code, name, vendor, question count, its free sample and test engine), so nothing is invented.
 * The visible FAQ section and the FAQPage markup both read these, which keeps them identical.
 */
class ExamFaqFallback
{
    /** @return array<int, array{question:string,answer:string}> */
    public static function for(Exam $exam): array
    {
        $code = trim((string) $exam->exam_code);
        if ($code === '') {
            return [];
        }
        $name = trim((string) $exam->exam_name);
        $vendor = $exam->vendor ? trim((string) $exam->vendor->name) : '';
        $site = app()->bound('current_site') && app('current_site') ? app('current_site')->name : config('app.name');
        $label = $name !== '' ? "{$code} {$name}" : $code;
        $questions = (int) ($exam->questions_count ?? $exam->questions()->count());

        $faqs = [[
            'question' => "What is the {$code} exam?",
            'answer' => "{$code} is the " . ($vendor !== '' ? "{$vendor} " : '') . ($name !== '' ? "{$name} " : '') . 'certification exam.'
                . ($vendor !== '' ? " Check {$vendor}'s official exam page for the current outline, languages, price and how to book." : ' Check the official exam page for the current outline, price and how to book.'),
        ]];

        if ($questions > 0) {
            $faqs[] = [
                'question' => "How many {$code} practice questions are there?",
                'answer' => "This page covers {$questions} {$code} practice questions with answers. The free sample shows how the questions and answers look before you choose a plan.",
            ];
        }

        $faqs[] = [
            'question' => "Is there a free {$code} practice test?",
            'answer' => "Yes. Request a free PDF sample of {$code} practice questions on the free sample page, or try the timed test engine. The full question set for {$label} comes with the plans shown on this page.",
        ];

        $faqs[] = [
            'question' => "Are these official {$code} exam questions?",
            'answer' => "No. They are independent practice questions based on the skills" . ($vendor !== '' ? " {$vendor} lists" : ' the vendor lists') . " for the exam. They are not official exam content, and {$site} is not affiliated with" . ($vendor !== '' ? " {$vendor}." : ' the exam vendor.'),
        ];

        $faqs[] = [
            'question' => "How should I prepare for {$code}?",
            'answer' => "Read the official study guide for {$code}, practise with the questions in timed sessions, review every answer you miss, and repeat until you score consistently well. Our blog has study guides for many exams.",
        ];

        // Microsoft publishes one passing score for its exams (checked against Microsoft Learn).
        if (stripos($vendor, 'microsoft') === 0 && ExamGuideContent::load((string) $exam->slug)) {
            $faqs[] = [
                'question' => "What is the passing score for {$code}?",
                'answer' => 'Microsoft reports exam results on a scale of 1 to 1,000 and a score of 700 or more is required to pass. It is a scaled score, not a percentage of questions answered correctly.',
            ];
        }

        return $faqs;
    }
}
