<?php

return [

    /*
    |--------------------------------------------------------------------------
    | View Storage Paths
    |--------------------------------------------------------------------------
    |
    | SITE_VIEW_OVERRIDE (e.g. "proexamtopics") names a folder under
    | resources/views-sites/ whose templates take priority over the shared ones in
    | resources/views. A site only needs to provide the templates whose design
    | differs; everything else falls through to the shared views.
    |
    */

    'paths' => array_values(array_filter([
        env('SITE_VIEW_OVERRIDE') ? resource_path('views-sites/' . env('SITE_VIEW_OVERRIDE')) : null,
        resource_path('views'),
    ])),

    /*
    |--------------------------------------------------------------------------
    | Compiled View Path
    |--------------------------------------------------------------------------
    */

    'compiled' => env(
        'VIEW_COMPILED_PATH',
        realpath(storage_path('framework/views'))
    ),

];
