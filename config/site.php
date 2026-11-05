<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Fixed Site Identity
    |--------------------------------------------------------------------------
    |
    | When APP_SITE_ID is set (e.g. 1 for ExamTopicsBase), the application operates
    | bound to this specific site ID, bypassing dynamic host-based resolution.
    |
    */
    'id' => env('APP_SITE_ID', 1),

    /*
    |--------------------------------------------------------------------------
    | Site Operating Mode
    |--------------------------------------------------------------------------
    |
    | Supported modes:
    | - 'standalone': Fixed site bound to 'id' (ExamTopicsBase default)
    | - 'dynamic': Host-based resolution matching incoming request against site_domains
    |
    */
    'mode' => env('APP_SITE_MODE', 'standalone'),

    /*
    |--------------------------------------------------------------------------
    | Public Exam URL Style
    |--------------------------------------------------------------------------
    |
    | 'prefixed': /exams/{vendor}/{slug}   (ExamTopicsBase)
    | 'root':     /{vendor}/{slug}-exam    (ProExamTopics)
    |
    */
    /*
    |--------------------------------------------------------------------------
    | Catalog Mode
    |--------------------------------------------------------------------------
    |
    | 'all':      every active exam appears automatically (hide one with its overlay)
    | 'assigned': only exams assigned to this site in the Websites admin appear
    |
    | The core site always shows the full catalog.
    |
    */
    'catalog_mode' => env('SITE_CATALOG_MODE', 'assigned'),

    /*
    |--------------------------------------------------------------------------
    | Vendor Restriction
    |--------------------------------------------------------------------------
    |
    | Comma separated vendor slugs this site is limited to (SITE_VENDORS=microsoft).
    | Empty = no restriction. When set, only these vendors' exams, certifications and
    | bundles exist on this site (lists, search, sitemap and direct URLs alike).
    |
    */
    'vendors' => array_values(array_filter(array_map('trim', explode(',', (string) env('SITE_VENDORS', ''))))),

    /*
    |--------------------------------------------------------------------------
    | Wording switches
    |--------------------------------------------------------------------------
    |
    | SITE_AVOID_DUMPS=true   replaces "dumps" in generated exam titles/descriptions/keywords.
    | SITE_SHOW_GUARANTEE=false  hides the "100% Pass Guarantee" card on exam pages
    |                            (use it until a refund policy has been agreed for the site).
    |
    */
    'avoid_dumps' => filter_var(env('SITE_AVOID_DUMPS', false), FILTER_VALIDATE_BOOLEAN),
    // Free practice questions shown on a hub-style exam's questions page (5 per page).
    'free_questions' => max(1, (int) env('SITE_FREE_QUESTIONS', 10)),

    // Raster logo for Organization markup (search engines reject SVG there), e.g. SITE_SCHEMA_LOGO=images/logo-primary.png
    'schema_logo' => env('SITE_SCHEMA_LOGO'),

    'show_guarantee' => filter_var(env('SITE_SHOW_GUARANTEE', true), FILTER_VALIDATE_BOOLEAN),

    'exam_url_style' => env('EXAM_URL_STYLE', 'prefixed'),

];
