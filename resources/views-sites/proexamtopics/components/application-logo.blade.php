@php
    $siteContext = app(\App\Services\SiteContext::class);
    $activeSite = $siteContext->site();
    $branding = $siteContext->branding();
    $isSiteB = ($activeSite && $activeSite->id > 1) || (int) config('site.id', 1) === 2;

    $rawLogo = $activeSite?->logo ?: ($isSiteB ? null : ($branding['logo'] ?? null));
    if ($isSiteB && $rawLogo && ($rawLogo === 'images/logo.png' || str_contains(strtolower($rawLogo), 'examtopicsbase'))) {
        $rawLogo = null;
    }

    $logoUrl = $rawLogo ? asset($rawLogo) : null;
    $brandName = $activeSite ? $activeSite->name : ($branding['name'] ?? config('app.name', 'ProExamTopics'));
    $isDark = isset($attributes) ? (bool)$attributes->get('dark', false) : false;
@endphp

@if($logoUrl)
    @if($isDark)
        <div class="inline-flex items-center bg-white px-3.5 py-1.5 rounded-xl shadow-md border border-slate-700/30">
            <img src="{{ $logoUrl }}" alt="{{ $brandName }}" class="h-9 md:h-10 w-auto object-contain">
        </div>
    @else
        <img src="{{ $logoUrl }}" alt="{{ $brandName }}" {{ $attributes->merge(['class' => 'h-11 md:h-12 w-auto max-h-14 object-contain transition-transform duration-200 group-hover:scale-105']) }}>
    @endif
@else
    <span {{ isset($attributes) ? $attributes->merge(['class' => 'inline-flex items-center gap-2.5 tracking-tight ' . ($isDark ? 'text-white' : 'text-slate-900')]) : 'class="inline-flex items-center gap-2.5 tracking-tight ' . ($isDark ? 'text-white' : 'text-slate-900') . '"' }}>
        <span class="w-8 h-8 flex items-center justify-center shrink-0">
            <svg class="w-7 h-7 text-[#00D4AA]" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M16 2.5L27.5 9.1V22.9L16 29.5L4.5 22.9V9.1L16 2.5Z" stroke="#00D4AA" stroke-width="2.6" stroke-linejoin="round" fill="none"/>
                <path d="M11 16L14.5 19.5L21.5 12.5" stroke="#00D4AA" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        </span>
        <span class="text-xl font-bold tracking-tight {{ $isDark ? 'text-white' : 'text-slate-900' }}">{{ $brandName }}</span>
    </span>
@endif