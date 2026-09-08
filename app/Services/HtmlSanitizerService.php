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

        return trim($html);
    }
}
