@php
    // The sign-in pages sit on a dark background, so a site with its own logo uses the white ("reversed") version when it has one.
    $siteForLogo = app()->bound('current_site') ? app('current_site') : null;
    $siteLogo = ($siteForLogo && !empty($siteForLogo->logo)) ? $siteForLogo->logo : null;
    if ($siteLogo) {
        $reversed = str_replace('primary', 'reversed', $siteLogo);
        $siteLogo = is_file(public_path($reversed)) ? $reversed : $siteLogo;
    }
@endphp
@if($siteLogo)
    <img src="{{ asset($siteLogo) }}" alt="{{ $siteForLogo->name }} logo" width="270" height="30" {{ $attributes }}>
@else
    <img src="{{ asset('images/logo.png') }}?v={{ filemtime(public_path('images/logo.png')) }}" alt="{{ config('app.name', 'ExamTopicsBase') }}" {{ $attributes }}>
@endif
