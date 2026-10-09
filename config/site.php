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

    'exam_url_style' => env('EXAM_URL_STYLE', 'prefixed'),

];
