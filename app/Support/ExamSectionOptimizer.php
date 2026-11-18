<?php

namespace App\Support;

use App\Models\Exam;

/**
 * Reworks the three stored sections of an exam page (Exam Details, Topics Covered, FAQ) around the keyword groups in
 * docs/keyword-research-core.md (passing score, exam cost, objectives/syllabus, free practice test, study guide).
 *
 * It never adds a fact that is not already on the page or in the vendor data we hold:
 *  - the "quick answer" is built only from the page's own details table,
 *  - extra FAQ answers use that table (or the exam's topic list) and otherwise point to the vendor's official page.
 */
class ExamSectionOptimizer
{
    /** Row label (lower case, start of label) => fact key. */
    private const ROW_MAP = [
        'duration'     => '/^(exam duration|testing duration)/',
        'passing'      => '/^passing (score|standard)/',
        'fee'          => '/^(official exam fee|exam cost|exam registration fee|registration fee|exam price)\b/',
        'delivery'     => '/^(exam delivery|delivery methods?|testing delivery|delivery model)/',
        'format'       => '/^(question formats?|question types)/',
        'certificate'  => '/^(certification name|associated certification|associated credential|target credential)/',
        'prerequisite' => '/^(prerequisites|exam prerequisites)/',
    ];

    /** @return array<string,string> the facts found in the exam's details table (may be empty) */
    public static function facts(Exam $exam): array
    {
        $html = (string) $exam->article_content;
        if ($html === '' || stripos($html, '<t') === false) {
            return [];
        }
        // one line per table row: cells are separated by "|", and paragraphs inside a cell must not break the row
        $text = str_replace(["\r", "\n"], ' ', $html);
        $text = str_replace(['</td>', '</th>'], ' | ', $text);
        $text = preg_replace('/<tr\b/i', "\n<tr", $text);   // a row starts on its own line, so text before the table cannot join it
        $text = str_replace('</tr>', "\n", $text);
        $text = html_entity_decode(strip_tags($text), ENT_QUOTES | ENT_HTML5);

        $facts = [];
        foreach (preg_split('/\R/', $text) as $line) {
            $cells = array_values(array_filter(array_map('trim', explode('|', $line)), fn ($c) => $c !== ''));
            if (count($cells) !== 2) {
                continue;
            }
            $label = mb_strtolower(preg_replace('/\s+/', ' ', $cells[0]));
            foreach (self::ROW_MAP as $key => $rx) {
                if (!isset($facts[$key]) && preg_match($rx, $label)) {
                    $facts[$key] = trim(preg_replace('/\s+/', ' ', $cells[1]));
                }
            }
        }

        return $facts;
    }

    /** One plain-text sentence group answering the questions people search first, from the page's own table. */
    public static function quickAnswer(Exam $exam, array $facts): ?string
    {
        $vendor = (string) optional($exam->vendor)->name;
        $parts = [];
        if (!empty($facts['passing'])) {
            $parts[] = "a passing score of {$facts['passing']}";
        }
        if (!empty($facts['duration'])) {
            $parts[] = "a duration of {$facts['duration']}";
        }
        if (!empty($facts['delivery'])) {
            $parts[] = 'delivery by ' . rtrim($facts['delivery'], '. ');
        }
        if (count($parts) < 2) {
            return null;
        }
        $code = trim((string) $exam->exam_code);
        $name = trim((string) $exam->exam_name);
        $text = "{$code}" . ($name !== '' ? " ({$name})" : '') . ' is a' . ($vendor !== '' ? " {$vendor}" : '') . ' certification exam. The details below list ' . implode(', ', $parts) . '.';
        if (!empty($facts['fee'])) {
            $text .= " The listed exam fee is {$facts['fee']}; fees are set by " . ($vendor !== '' ? $vendor : 'the vendor') . ', vary by country and change, so confirm it on the official page.';
        }

        return $text;
    }

    /** Rewrites the headings of the stored article and adds the quick answer under "Exam Details". */
    public static function optimizeArticle(string $html, Exam $exam): string
    {
        if (trim($html) === '' || !class_exists(\DOMDocument::class)) {
            return $html;
        }
        $facts = static::facts($exam);
        $lead = static::quickAnswer($exam, $facts);
        $hasFacts = !empty($facts['passing']) || !empty($facts['duration']);

        $dom = new \DOMDocument('1.0', 'UTF-8');
        $prev = libxml_use_internal_errors(true);
        $dom->loadHTML('<?xml encoding="utf-8"?><div id="__wrap">' . $html . '</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();
        libxml_use_internal_errors($prev);

        $leadDone = false;
        foreach (iterator_to_array($dom->getElementsByTagName('h2')) as $h2) {
            $text = trim(preg_replace('/\s+/', ' ', $h2->textContent));
            $new = null;
            if (preg_match('/^(.*\S)\s+Exam Details$/i', $text, $m)) {
                $new = $m[1] . ' Exam Details: ' . ($hasFacts ? 'Passing Score, Format and Requirements' : 'Format and Requirements');
                if ($lead && !$leadDone) {
                    $p = $dom->createElement('p');
                    $strong = $dom->createElement('strong', 'Quick answer: ');
                    $p->appendChild($strong);
                    $p->appendChild($dom->createTextNode($lead));
                    if ($h2->nextSibling) {
                        $h2->parentNode->insertBefore($p, $h2->nextSibling);
                    } else {
                        $h2->parentNode->appendChild($p);
                    }
                    $leadDone = true;
                }
            } elseif (preg_match('/^What the (.+?) Exam Covers\??$/i', $text, $m)) {
                $new = $m[1] . ' Exam Objectives: What the Exam Covers';
            }
            if ($new !== null) {
                while ($h2->firstChild) {
                    $h2->removeChild($h2->firstChild);
                }
                $h2->appendChild($dom->createTextNode($new));
            }
        }

        $wrap = $dom->getElementById('__wrap') ?? (new \DOMXPath($dom))->query('//div[@id="__wrap"]')->item(0);
        $out = '';
        foreach ($wrap->childNodes as $child) {
            $out .= $dom->saveHTML($child);
        }

        return $out;
    }

    public static function topicsHeading(Exam $exam): string
    {
        return trim((string) $exam->exam_code) . ' Exam Objectives and Topics Covered';
    }

    public static function topicsIntro(Exam $exam): string
    {
        $code = trim((string) $exam->exam_code);
        $name = trim((string) $exam->exam_name);

        return "These are the {$code} exam objectives and topics" . ($name !== '' ? " for the {$name} exam" : '') . '. Use them to plan your revision and to check each area against the practice questions.';
    }

    /**
     * Adds the questions people search for when the page does not answer them yet (passing score, cost, objectives,
     * a free practice test, how to study). Existing questions are never changed or removed.
     *
     * @param  array<int, array{question?:string,answer?:string}>  $faqs
     * @return array<int, array{question:string,answer:string}>
     */
    public static function mergeFaqs(Exam $exam, array $faqs): array
    {
        $code = trim((string) $exam->exam_code);
        if ($code === '') {
            return $faqs;
        }
        $vendor = (string) optional($exam->vendor)->name ?: 'the vendor';
        $site = app()->bound('current_site') && app('current_site') ? app('current_site')->name : config('app.name');
        $facts = static::facts($exam);
        $asked = mb_strtolower(implode(' | ', array_map(fn ($f) => (string) ($f['question'] ?? ''), $faqs)));
        $out = $faqs;
        $add = function (string $pattern, string $q, string $a) use (&$out, $asked) {
            if (!preg_match($pattern, $asked)) {
                $out[] = ['question' => $q, 'answer' => $a];
            }
        };

        $add('/passing|pass score|cut score|score needed/', "What is the passing score for {$code}?",
            !empty($facts['passing'])
                ? "The {$code} exam details on this page list a passing score of {$facts['passing']}. {$vendor} sets the passing score and can change it, so confirm the current figure on the official exam page."
                : "{$vendor} sets the passing score for {$code} and reports it with your results. Check the official exam page for the current figure.");

        $add('/\bcost|\bfee\b|\bprice|how much/', "How much does the {$code} exam cost?",
            !empty($facts['fee'])
                ? "The exam details on this page list an official fee of {$facts['fee']}. Exam fees are set by {$vendor}, vary by country or region and can change, so check the official page before you book. Practice material from {$site} is sold separately."
                : "{$vendor} sets the {$code} exam fee, and it varies by country or region. Check the official exam page for the current price. Practice material from {$site} is sold separately.");

        $topics = is_array($exam->topics) ? array_values(array_filter(array_map('strval', $exam->topics))) : [];
        $add('/objective|syllabus|domain|blueprint|skills measured|topics/', "What are the {$code} exam objectives?",
            $topics
                ? "The {$code} objectives listed on this page are: " . implode('; ', array_slice($topics, 0, 6)) . (count($topics) > 6 ? '; and more.' : '.') . " {$vendor} publishes the current outline on its official exam page."
                : "{$vendor} publishes the {$code} exam objectives in its official exam outline. Use the official links on this page to confirm the current list before you start studying.");

        $add('/free/', "Is there a free {$code} practice test?",
            "Yes. Request a free PDF sample of {$code} practice questions on the free sample page, or try the timed test engine.");

        $add('/study|prepare|preparation|revise/', "What is the best way to study for {$code}?",
            "Start with the official study guide and the exam objectives, practise with exam-style questions in timed sessions, review every answer you miss, and repeat until you score consistently well.");

        return array_slice($out, 0, 12);
    }
}
