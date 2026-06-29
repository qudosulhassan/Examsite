<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default SEO Settings
    |--------------------------------------------------------------------------
    |
    | These values are used as fallbacks when a specific page does not have
    | its own custom SEO metadata defined.
    |
    */

    'site_name' => 'ExamsNinja',
    'tagline' => 'Pass Like a Ninja. First Attempt Guaranteed.',
    'title_separator' => '|',
    
    'defaults' => [
        'title' => 'ExamsNinja - Pass Your IT Certification Exam First Attempt',
        'description' => 'Prepare for any IT certification exam with ExamsNinja. Download verified PDF dumps or practice online with our test engine. 3,500+ exams. 99.6% pass rate.',
        'keywords' => 'IT certification, exam dumps, study guides, practice tests, exam engine',
        'og_image' => 'images/og-default.png',
        'og_type' => 'website',
        'robots' => 'index, follow',
    ],

    'social' => [
        'twitter_handle' => '@ExamsNinja',
    ],

    'tracking' => [
        // Load from .env if present
        'ga4_measurement_id' => env('GA4_MEASUREMENT_ID', ''),
    ],
    
    'verification' => [
        'google_search_console' => env('GOOGLE_SITE_VERIFICATION', ''),
    ],

];
