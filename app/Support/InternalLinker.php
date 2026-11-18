<?php

namespace App\Support;

use App\Models\Exam;
use Illuminate\Support\Facades\Cache;

/**
 * Turns the first mention of another exam's code ("AZ-900", "200-301") in an exam page's text into a link to that
 * exam's page. Works on text only (never inside an existing link, heading, button or script), skips the page's own
 * code, and adds at most a few links per section so the page does not become a wall of links.
 */
class InternalLinker
{
    private const CODE_RX = '/\b[A-Za-z]{1,6}-?\d{2,4}[A-Za-z0-9_-]*\b/';

    /** @return array<string,string> lowercase exam code => exam page URL */
    public static function index(): array
    {
        return Cache::remember('exam_code_link_index_' . (int) config('site.id', 1), 3600, function () {
            $map = [];
            foreach (Exam::where('is_active', true)->with('vendor')->get(['id', 'vendor_id', 'exam_code', 'slug']) as $e) {
                $code = strtolower(trim((string) $e->exam_code));
                if (strlen($code) >= 5 && $e->vendor) {
                    $map[$code] = $e->url;
                }
            }

            return $map;
        });
    }

    /** Plain text in, safe HTML out (escaped, with links). */
    public static function linkText(string $text, Exam $exam, int $max = 3, ?array $index = null): string
    {
        $index ??= static::index();
        $own = strtolower(trim((string) $exam->exam_code));
        $used = [];
        $html = '';
        $pos = 0;

        if (preg_match_all(self::CODE_RX, $text, $m, PREG_OFFSET_CAPTURE)) {
            foreach ($m[0] as [$token, $offset]) {
                $key = strtolower($token);
                if ($key === $own || !isset($index[$key]) || isset($used[$key]) || count($used) >= $max) {
                    continue;
                }
                $html .= e(substr($text, $pos, $offset - $pos));
                $html .= '<a href="' . e($index[$key]) . '" class="underline">' . e($token) . '</a>';
                $pos = $offset + strlen($token);
                $used[$key] = true;
            }
        }

        return $html . e(substr($text, $pos));
    }

    /** HTML in, HTML out: links are added only inside plain text nodes. */
    public static function linkHtml(string $html, Exam $exam, int $max = 5): string
    {
        if (trim($html) === '' || !class_exists(\DOMDocument::class)) {
            return $html;
        }
        $index = static::index();
        $own = strtolower(trim((string) $exam->exam_code));
        $used = [];

        $dom = new \DOMDocument('1.0', 'UTF-8');
        $prev = libxml_use_internal_errors(true);
        $dom->loadHTML('<?xml encoding="utf-8"?><div id="__wrap">' . $html . '</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();
        libxml_use_internal_errors($prev);

        $xp = new \DOMXPath($dom);
        $nodes = $xp->query('//div[@id="__wrap"]//text()[not(ancestor::a) and not(ancestor::h1) and not(ancestor::h2) and not(ancestor::h3) and not(ancestor::h4) and not(ancestor::h5) and not(ancestor::h6) and not(ancestor::script) and not(ancestor::style) and not(ancestor::button)]');

        foreach (iterator_to_array($nodes) as $node) {
            if (count($used) >= $max) {
                break;
            }
            $text = $node->nodeValue;
            if (!preg_match_all(self::CODE_RX, $text, $m, PREG_OFFSET_CAPTURE)) {
                continue;
            }
            $frag = $dom->createDocumentFragment();
            $pos = 0;
            $changed = false;
            foreach ($m[0] as [$token, $offset]) {
                $key = strtolower($token);
                if ($key === $own || !isset($index[$key]) || isset($used[$key]) || count($used) >= $max) {
                    continue;
                }
                $frag->appendChild($dom->createTextNode(substr($text, $pos, $offset - $pos)));
                $a = $dom->createElement('a', $token);
                $a->setAttribute('href', $index[$key]);
                $frag->appendChild($a);
                $pos = $offset + strlen($token);
                $used[$key] = true;
                $changed = true;
            }
            if ($changed) {
                $frag->appendChild($dom->createTextNode(substr($text, $pos)));
                $node->parentNode->replaceChild($frag, $node);
            }
        }

        $wrap = $dom->getElementById('__wrap') ?? $xp->query('//div[@id="__wrap"]')->item(0);
        $out = '';
        foreach ($wrap->childNodes as $child) {
            $out .= $dom->saveHTML($child);
        }

        return $out;
    }
}
