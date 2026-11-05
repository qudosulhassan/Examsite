@extends('layouts.public')

@php
    $brand = $currentSite ? $currentSite->name : config('app.name');
    $vendorName = \App\Models\Vendor::where('slug', (string) (config('site.vendors')[0] ?? ''))->value('name') ?: 'Microsoft';
    // One data source: the visible questions and the FAQPage markup below both come from $faqs.
    $faqs = \App\Support\SiteFaq::general($brand, $vendorName);
@endphp

@section('title', "FAQ | {$brand}")
@section('meta_description', \App\Support\SeoMeta::staticPage('faq', $brand, $vendorName)['description'])

@section('seo_tags')
    <script type="application/ld+json">
    {!! json_encode(\App\Support\SiteFaq::schema($faqs), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
    </script>
@endsection

@section('content')
<section class="border-b border-slate-200 bg-white">
    <div class="container-custom py-10 lg:py-14">
        <nav aria-label="Breadcrumb" class="text-sm text-slate-500">
            <ol class="flex flex-wrap items-center gap-x-2">
                <li><a href="{{ url('/') }}" class="hover:text-slate-900">Home</a></li>
                <li aria-hidden="true">/</li>
                <li aria-current="page" class="font-semibold text-slate-800">FAQ</li>
            </ol>
        </nav>
        <h1 class="mt-4 text-3xl font-extrabold leading-tight text-slate-900 sm:text-4xl">Frequently asked questions about {{ $brand }}</h1>
        <p class="mt-3 max-w-3xl text-base leading-relaxed text-slate-600 sm:text-lg">
            How the practice questions, PDF guides and test engine work, how we use {{ $vendorName }}'s official study guides, and how to get help.
        </p>
    </div>
</section>

<section class="py-10 lg:py-14">
    <div class="container-custom max-w-4xl">
        <div class="space-y-3" x-data="{ open: 0 }">
            @foreach($faqs as $i => $faq)
                <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white">
                    <h2 class="text-base">
                        <button type="button" @click="open = open === {{ $i }} ? null : {{ $i }}" class="flex w-full items-center justify-between gap-4 px-5 py-4 text-left" :aria-expanded="open === {{ $i }}">
                            <span class="font-bold text-slate-900">{{ $faq['q'] }}</span>
                            <svg class="h-5 w-5 flex-none text-slate-400 transition" :class="open === {{ $i }} ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                    </h2>
                    {{-- All answers are in the HTML for crawlers; the classes only hide them visually --}}
                    <div x-cloak :class="open === {{ $i }} ? '' : 'hidden'" class="border-t border-slate-100 px-5 py-4 text-sm leading-relaxed text-slate-600">{{ $faq['a'] }}</div>
                </div>
            @endforeach
        </div>

        <p class="mt-8 text-sm text-slate-600">
            Still have a question? <a href="{{ url('/contact') }}" class="font-bold underline" style="color: var(--brand);">Contact us</a> or browse the
            <a href="{{ url('/vendors/' . (config('site.vendors')[0] ?? 'microsoft')) }}" class="font-bold underline" style="color: var(--brand);">{{ $vendorName }} exams</a>.
        </p>
    </div>
</section>
@endsection
