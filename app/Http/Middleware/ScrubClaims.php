<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Rewrites a few marketing phrases in public pages that come from the database (exam descriptions, blog posts,
 * reviews), where editing the templates cannot reach them. Switched on per site with SITE_CLEAN_CLAIMS=true.
 *
 * It is deliberately narrow: "exam dumps" / "braindumps" / "<exam code> dumps" and "real/actual exam" only. A bare
 * "dumps" is left alone because "core dumps" and "database dumps" are real IT terms inside practice questions.
 */
class ScrubClaims
{
    private const SKIP = '#^(admin|dashboard|api|checkout|cart|login|register|password|webhook|demo-test-engine/session)#';

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (!config('site.clean_claims') || !$request->isMethod('GET') || $response->getStatusCode() !== 200) {
            return $response;
        }
        if (preg_match(self::SKIP, ltrim($request->path(), '/'))) {
            return $response;
        }
        if (!str_contains((string) $response->headers->get('Content-Type'), 'text/html')) {
            return $response;
        }

        $content = $response->getContent();
        if (is_string($content) && $content !== '') {
            $response->setContent(static::clean($content));
        }

        return $response;
    }

    public static function clean(string $html): string
    {
        // "exam dumps", "real dumps", "latest dumps" ... -> "exam questions", "real questions" ...
        $html = preg_replace_callback('/\b(exam|real|latest|actual|free|verified|updated|valid|premium)\s+(?:brain)?dumps\b/i', function ($m) {
            return static::keepCase($m[1] . ' questions', $m[0]);
        }, $html);
        // "braindumps" -> "practice questions"
        $html = preg_replace('/\bbraindumps?\b/i', 'practice questions', $html);
        // "AZ-900 dumps" -> "AZ-900 practice questions"
        $html = preg_replace('/\b([A-Za-z]{0,4}-?\d{2,4}(?:-[A-Za-z0-9]+)?)\s+dumps\b/', '$1 practice questions', $html);
        // "real exam", "actual exams" -> "exam", "exams"
        $html = preg_replace_callback('/\b(?:real|actual)\s+(exams?)\b/i', function ($m) {
            return static::keepCase($m[1], $m[0]);
        }, $html);

        return $html;
    }

    private static function keepCase(string $new, string $old): string
    {
        return ctype_upper(substr($old, 0, 1)) ? ucfirst($new) : $new;
    }
}
