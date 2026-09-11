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

];
