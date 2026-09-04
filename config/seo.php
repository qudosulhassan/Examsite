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

    'site_name' => 'Exam Topics Base',
    'tagline' => 'Pass Like a Ninja. First Attempt Guaranteed.',
    'title_separator' => '|',
    
    'defaults' => [
        'title' => 'Exam Topics Base - Pass Your IT Certification Exam First Attempt',
        'description' => 'Prepare for any IT certification exam with Exam Topics Base. Download verified PDF dumps or practice online with our test engine. 3,500+ exams. 99.6% pass rate.',
        'keywords' => 'IT certification, exam dumps, study guides, practice tests, exam engine',
        'og_image' => 'images/og-default.png',
        'og_type' => 'website',
        'robots' => env('SEO_ROBOTS', 'noindex, nofollow'),
    ],

    'social' => [
        'twitter_handle' => '@Exam Topics Base',
    ],

    'tracking' => [
        // Load from .env if present
        'ga4_measurement_id' => env('GA4_MEASUREMENT_ID', ''),
    ],
    
    'verification' => [
        'google_search_console' => env('GOOGLE_SITE_VERIFICATION', ''),
    ],

];
