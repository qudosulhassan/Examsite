<?php

namespace App\Support;

/**
 * The questions and answers about the site itself, used by the FAQ page and its FAQPage markup so the visible
 * text and the structured data can never drift apart.
 *
 * Deliberately not covered yet (the owner has not supplied the facts): which payment methods are accepted, and
 * the refund policy. Add them here when they are decided; both the page and the markup pick them up.
 */
class SiteFaq
{
    /** @return array<int, array{q: string, a: string}> */
    public static function general(string $brand, string $vendor = 'Microsoft'): array
    {
        return [
            ['q' => "What is {$brand}?",
             'a' => "{$brand} is an independent exam-preparation site for {$vendor} certification exams. Each exam has a guide that follows {$vendor}'s official skills outline, a page of free practice questions with explanations, and plans that add a larger question bank, a timed online test engine and a PDF study guide."],
            ['q' => "Is {$brand} affiliated with {$vendor}?",
             'a' => "No. {$brand} is not produced, endorsed or approved by {$vendor}. Exam names and certification titles belong to {$vendor}. Our material is for study and practice and is not official exam content."],
            ['q' => 'Are the free practice questions really free?',
             'a' => 'Yes. Open the free practice questions page of any exam, answer the questions, check each answer and read the explanation. You do not need an account or a card.'],
            ['q' => 'How does the practice test engine work?',
             'a' => 'The test engine runs in your browser. You can practise question by question with instant feedback and an explanation, or switch to a timed exam mode that scores you at the end. Afterwards you can review every answer to find the topics that need more work.'],
            ['q' => 'What is the difference between the PDF study guide and the test engine?',
             'a' => 'The PDF is a printable study guide you can read offline on any device. The test engine is online and interactive: it scores you, times you and lets you review your answers. The bundle gives you both for one exam.'],
            ['q' => "Do you sell real {$vendor} exam questions or \"dumps\"?",
             'a' => "No. We do not publish or sell real exam questions or braindumps, and we encourage you not to use them: {$vendor} prohibits sharing real exam content, and memorised answers do not prove you can do the job. Our practice questions help you test your understanding of the skills an exam measures."],
            ['q' => 'How are the exam guides put together?',
             'a' => "Each guide is built from the exam's official study guide on Microsoft Learn: the domains, their weightings, the listed skills and the date Microsoft last updated the outline. We add a study plan and FAQs to help you use that outline, and every guide links back to the official page."],
            ['q' => 'How often are the questions and guides updated?',
             'a' => 'Every plan includes three months of updates from the day you buy, and you can add six or twelve months. Each exam page shows when its question bank was last updated, and the guide shows the date of the official skills outline it follows. Microsoft changes and retires exams from time to time, so check the official study guide before you book.'],
            ['q' => 'How do I get my material after I buy?',
             'a' => 'Access is added to your account as soon as your order is confirmed. Sign in and open your dashboard to download your PDF guides or start the test engine. You also receive an email confirmation.'],
            ['q' => 'Which devices can I use?',
             'a' => 'The test engine works in a modern web browser on a phone, tablet or computer. PDF guides open on any device with a PDF reader.'],
            ['q' => 'Do I need an account?',
             'a' => 'Not for the free practice questions. You need an account to buy a plan, because your PDFs and test engine access are kept in your dashboard.'],
            ['q' => 'How many times can I download a PDF?',
             'a' => 'You can download each purchased PDF up to three times, so keep your copy somewhere safe.'],
            ['q' => 'What should I do if I think a question or explanation is wrong?',
             'a' => 'Tell us. Send the exam code, the question number and what you think is wrong through the contact page. We check it against the official documentation and correct it when it is a mistake.'],
            ['q' => 'How do I contact support?',
             'a' => 'Use the contact page and tell us your exam code and, for an order, the email address you used. It helps us answer faster.'],
        ];
    }

    /** The FAQPage JSON-LD for any list of questions, generated from the same array that the page prints. */
    public static function schema(array $faqs): array
    {
        return [
            '@context'   => 'https://schema.org',
            '@type'      => 'FAQPage',
            'mainEntity' => array_map(fn ($f) => [
                '@type'          => 'Question',
                'name'           => $f['q'],
                'acceptedAnswer' => ['@type' => 'Answer', 'text' => $f['a']],
            ], $faqs),
        ];
    }
}
