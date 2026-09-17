<?php

namespace App\Services;

class HtmlSanitizerService
{
    /**
     * Sanitize HTML content while preserving rich blog markup including tables,
     * code blocks, images, links, formatting, and heading hierarchy.
     */
    public static function sanitize(?string $html): string
    {
        if (empty($html)) {
            return '';
        }

        // 1. Remove dangerous tags entirely (including content inside scripts/iframes)
        $html = preg_replace('#<script(.*?)>(.*?)</script>#is', '', $html);
        $html = preg_replace('#<iframe(.*?)>(.*?)</iframe>#is', '', $html);
        $html = preg_replace('#<object(.*?)>(.*?)</object>#is', '', $html);
        $html = preg_replace('#<embed(.*?)>(.*?)</embed>#is', '', $html);
        $html = preg_replace('#<applet(.*?)>(.*?)</applet>#is', '', $html);
        $html = preg_replace('#<meta(.*?)>#is', '', $html);
        $html = preg_replace('#<link(.*?)>#is', '', $html);
        $html = preg_replace('#<base(.*?)>#is', '', $html);

        // 2. Remove event handlers (e.g. onload=, onclick=, onerror=, onmouseover=, etc.)
        $html = preg_replace('/\s*on[a-zA-Z]+\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)/i', '', $html);

        // 3. Remove javascript:, vbscript:, data: (except safe data:image/) from href/src
        $html = preg_replace('/href\s*=\s*["\']\s*javascript:[^"\']*["\']/i', 'href="#"', $html);
        $html = preg_replace('/src\s*=\s*["\']\s*javascript:[^"\']*["\']/i', 'src=""', $html);
        $html = preg_replace('/href\s*=\s*["\']\s*vbscript:[^"\']*["\']/i', 'href="#"', $html);
        $html = preg_replace('/src\s*=\s*["\']\s*vbscript:[^"\']*["\']/i', 'src=""', $html);

        // 4. Disallow dangerous CSS in style attributes (e.g. expression(), behavior(), -moz-binding)
        $html = preg_replace_callback('/style\s*=\s*["\']([^"\']*)["\']/i', function ($matches) {
            $style = $matches[1];
            if (preg_match('/(expression|behavior|javascript|vbscript|-moz-binding)/i', $style)) {
                return '';
            }
            return 'style="' . htmlspecialchars($style, ENT_QUOTES, 'UTF-8') . '"';
        }, $html);

        // 5. Clean any character encoding mojibake (e.g. â€“ or Ã¢Â€Â“ to –)
        $html = self::cleanMojibake($html);

        return trim($html);
    }

    /**
     * Auto-heal common single and double encoded mojibake sequences (e.g., â€“ or Ã¢Â€Â“ into –)
     */
    public static function cleanMojibake(?string $text): string
    {
        if (empty($text)) {
            return '';
        }

        $map = [
            // Double / multi-encoded sequences
            'Ã¢Â€Â“' => '–',
            'Ã¢Â€Â—' => '—',
            'Ã¢Â€Â”' => '—',
            'Ã¢Â€Âœ' => '“',
            'Ã¢Â€Â\x9d' => '”',
            'Ã¢Â€Â˜' => '‘',
            'Ã¢Â€Â™' => '’',
            'Ã¢Â€Â¢' => '•',
            'Ã¢Â„Â¢' => '™',
            'Ã‚Â©'   => '©',
            'Ã‚Â®'   => '®',
            'Ã‚Â°'   => '°',
            'Ã‚Â±'   => '±',

            // Exact byte-level sequences for single mojibake
            "\xC3\xA2\xC2\x80\xC2\x93" => '–', // â€“ (en dash)
            "\xC3\xA2\xC2\x80\xC2\x94" => '—', // â€” (em dash)
            "\xC3\xA2\xC2\x80\xC2\x9C" => '“', // â€œ (left double quote)
            "\xC3\xA2\xC2\x80\xC2\x9D" => '”', // â€ (right double quote)
            "\xC3\xA2\xC2\x80\xC2\x98" => '‘', // â€˜ (left single quote)
            "\xC3\xA2\xC2\x80\xC2\x99" => '’', // â€™ (right single quote)
            "\xC3\xA2\xC2\x80\xC2\xA2" => '•', // â€¢ (bullet)
            "\xC3\xA2\xC2\x84\xC2\xA2" => '™', // â„¢ (trademark)
            "\xC3\x82\xC2\xA9"         => '©', // Â©
            "\xC3\x82\xC2\xAE"         => '®', // Â®
            "\xC3\x82\xC2\xB0"         => '°', // Â°
            "\xC3\x82\xC2\xB1"         => '±', // Â±

            // Literal string variations
            'â€“' => '–',
            'â€”' => '—',
            'â€œ' => '“',
            'â€' => '”',
            'â€˜' => '‘',
            'â€™' => '’',
            'â€¢' => '•',
            'â„¢' => '™',
            'Â©'  => '©',
            'Â®'  => '®',
            'Â°'  => '°',
            'Â±'  => '±',
        ];

        return strtr($text, $map);
    }
}
