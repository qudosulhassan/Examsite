@extends('layouts.public')

@php
    $brand = $currentSite ? $currentSite->name : ($globalSettings['site_name'] ?? config('app.name'));
    $vendorName = $exam->vendor->name ?? '';
    $code = $exam->exam_code;
    $examName = trim((string) $exam->exam_name);
    $money = fn ($v) => '$' . number_format((float) $v, ((float) $v == floor((float) $v)) ? 0 : 2);

    $hasPdf = (bool) $exam->is_pdf_available && $prices['pdf'] > 0;
    $hasEngine = (bool) $exam->is_engine_available && $prices['engine_single'] > 0;
    $hasBundle = $hasPdf && $hasEngine && $prices['combo'] > 0;
    $separate = ($hasPdf ? $prices['pdf'] : 0) + ($hasEngine ? $prices['engine_single'] : 0);
    $saving = $hasBundle ? round($separate - $prices['combo'], 2) : 0;
    $savingPct = ($hasBundle && $separate > 0 && $saving > 0) ? (int) round($saving / $separate * 100) : 0;
    $checkoutOpen = $stripeEnabled || $paypalEnabled;

    $offerPrices = array_filter([$hasPdf ? $prices['pdf'] : null, $hasEngine ? $prices['engine_single'] : null, $hasBundle ? $prices['combo'] : null]);
    $lowPrice = $offerPrices ? min($offerPrices) : null;

    $meta = \App\Support\SeoMeta::plans($code, $examName, $lowPrice ? $money($lowPrice) : null, $brand);
    $title = $meta['title'];
    $h1 = "{$code} practice test plans";
    $lede = "Choose a PDF study guide, an online test engine, or both for the {$examName} exam. Every price is shown, and nothing is added at checkout.";
    $description = $meta['description'];

    $faqs = [
        ['q' => 'What do I receive after I pay?', 'a' => 'Access is added to your account straight away. Open your dashboard to download the PDF or start the test engine. You will also receive an email confirmation.'],
        ['q' => 'What is the difference between the PDF and the test engine?', 'a' => 'The PDF is a printable study guide you can read offline. The test engine is online: it offers a timed exam mode and an untimed practice mode with instant scoring and a review of every answer. The bundle gives you both.'],
        ['q' => 'What do the update periods mean?', 'a' => 'Three months of updates are included. You can add six or twelve months at the price shown above, so the material you bought stays current while you prepare.'],
        ['q' => 'How many times can I download the PDF?', 'a' => 'Each purchased PDF can be downloaded up to three times, so keep your copy somewhere safe.'],
        ['q' => 'Can I try before I buy?', 'a' => "Yes. Answer the free {$code} practice questions first. There is no account needed."],
        ['q' => 'Are these official ' . $vendorName . ' materials?', 'a' => "No. {$brand} is independent and not affiliated with {$vendorName}. Our materials are not official exam content, so check the provider's own exam page for the current skills outline."],
    ];

    $crumbs = [['name' => 'Home', 'url' => url('/')]];
    if (!empty($track)) { $crumbs[] = ['name' => $track['title'], 'url' => route('tracks.show', $track['slug'])]; }
    $crumbs[] = ['name' => $code, 'url' => $exam->url];
    $crumbs[] = ['name' => 'Plans', 'url' => $exam->plans_url];

    $offers = [];
    foreach ([['pdf', 'PDF study guide', $hasPdf], ['engine_single', 'Online test engine', $hasEngine], ['combo', 'PDF and test engine bundle', $hasBundle]] as [$key, $label, $on]) {
        if ($on) {
            $offers[] = ['@type' => 'Offer', 'name' => "{$code} {$label}", 'price' => number_format($prices[$key], 2, '.', ''), 'priceCurrency' => 'USD', 'availability' => 'https://schema.org/InStock', 'url' => $exam->plans_url];
        }
    }

    $periodLabels = [3 => '3 months', 6 => '6 months', 12 => '12 months'];
@endphp

@section('title', $title)
@section('meta_description', $description)
@section('canonical_url', $exam->plans_url)
@section('og_type', 'product')
@section('seo_tags')
    @include('partials.schema-breadcrumbs', ['crumbs' => $crumbs])
    @if($offers)
    <script type="application/ld+json">
    {!! json_encode([
        '@context' => 'https://schema.org',
        '@type' => 'Product',
        'name' => "{$code} {$examName} practice test",
        'description' => $description,
        'sku' => $code,
        'url' => $exam->plans_url,
        'brand' => ['@type' => 'Brand', 'name' => $brand],
        'offers' => $offers,
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
    </script>
    @endif
@endsection

@section('content')
@include('partials.exam-header', ['exam' => $exam, 'active' => 'plans', 'h1' => $h1, 'lede' => $lede, 'track' => $track])

<section class="py-10 lg:py-14">
    <div class="container-custom space-y-14">

        @if(session('error'))
            <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-semibold text-red-700">{{ session('error') }}</div>
        @endif

        {{-- ============ PLAN CARDS ============ --}}
        <div x-data="{
                period: 3,
                prices: {{ json_encode(['pdf' => $prices['pdf'], 'engine_single' => $prices['engine_single'], 'combo' => $prices['combo']]) }},
                addons: {{ json_encode($addons) }},
                money(v) { return '$' + (Math.round(v * 100) / 100).toFixed(Math.round(v * 100) % 100 === 0 ? 0 : 2); },
                total(type) { return this.prices[type] + (this.addons[type][this.period] || 0); }
             }">

            <div class="mx-auto max-w-3xl text-center">
                <p class="text-slate-600">All plans include 3 months of updates. Add more time if you plan to study for longer.</p>
            </div>

            <div class="mx-auto mt-6 flex w-full max-w-md justify-center">
                <div class="inline-flex w-full rounded-2xl bg-slate-200/70 p-1" role="radiogroup" aria-label="Update period">
                    @foreach($periodLabels as $months => $label)
                        <button type="button" role="radio" :aria-checked="period === {{ $months }}" @click="period = {{ $months }}"
                                class="flex-1 rounded-xl px-3 py-2.5 text-sm font-bold transition"
                                :class="period === {{ $months }} ? 'bg-white text-slate-900 shadow' : 'text-slate-600 hover:text-slate-900'">
                            {{ $label }}@if($months === 3)<span class="block text-[11px] font-semibold text-slate-500">included</span>@endif
                        </button>
                    @endforeach
                </div>
            </div>
            <p class="mt-2 text-center text-xs text-slate-500">Months of updates</p>

            <div class="mt-8 grid grid-cols-1 gap-5 {{ ($hasPdf && $hasEngine) ? 'lg:grid-cols-3' : 'lg:grid-cols-2' }}">
                @foreach([
                    ['key' => 'pdf', 'on' => $hasPdf, 'name' => 'PDF study guide', 'blurb' => 'Read it anywhere, print it, annotate it.', 'features' => ['Questions with answers and explanations', 'Printable and works offline', 'Up to 3 downloads per purchase', '3 months of updates included']],
                    ['key' => 'engine_single', 'on' => $hasEngine, 'name' => 'Online test engine', 'blurb' => 'Practise under the clock and see where you stand.', 'features' => ['Timed exam mode and practice mode', 'Instant scoring and answer review', 'Works in any modern browser', '3 months of updates included']],
                    ['key' => 'combo', 'on' => $hasBundle, 'name' => 'Bundle: PDF and test engine', 'blurb' => 'Study from the guide, then test yourself.', 'features' => ['Everything in the PDF plan', 'Everything in the test engine plan', $saving > 0 ? 'Saves ' . $money($saving) . ' compared with buying both' : 'One purchase for both formats', '3 months of updates included'], 'best' => true],
                ] as $plan)
                    @continue(!$plan['on'])
                    <div class="relative flex flex-col rounded-3xl border bg-white p-7 shadow-sm {{ !empty($plan['best']) ? 'border-2 shadow-2xl lg:-my-3 lg:py-10' : 'border-slate-200' }}" @if(!empty($plan['best'])) style="border-color: var(--brand);" @endif>
                        @if(!empty($plan['best']))
                            <span class="absolute -top-3 left-6 rounded-full px-3 py-1 text-xs font-extrabold text-white" style="background: var(--brand);">Best value{{ $savingPct ? ' - save ' . $savingPct . '%' : '' }}</span>
                        @endif
                        <h3 class="text-lg font-extrabold text-slate-900">{{ $plan['name'] }}</h3>
                        <p class="mt-1 text-sm text-slate-600">{{ $plan['blurb'] }}</p>

                        <p class="mt-5 flex items-end gap-2">
                            <span class="text-4xl font-extrabold text-slate-900" x-text="money(total('{{ $plan['key'] }}'))">{{ $money($prices[$plan['key']] + ($addons[$plan['key']][3] ?? 0)) }}</span>
                            <span class="pb-1 text-sm font-semibold text-slate-500">one-time</span>
                        </p>
                        <p class="mt-1 min-h-[1.25rem] text-xs text-slate-500">
                            <span x-cloak :class="period !== 3 ? '' : 'hidden'">includes <span x-text="money(addons['{{ $plan['key'] }}'][period] || 0)"></span> for <span x-text="period"></span> months of updates</span>
                        </p>

                        <ul class="mt-5 flex-1 space-y-2.5 text-sm text-slate-700">
                            @foreach($plan['features'] as $feature)
                                <li class="flex gap-2.5"><span class="mt-0.5 flex-none font-bold" style="color: var(--brand);">&#10003;</span><span>{{ $feature }}</span></li>
                            @endforeach
                        </ul>

                        @if($checkoutOpen)
                            <form method="POST" action="{{ route('cart.add') }}" class="mt-6">
                                @csrf
                                <input type="hidden" name="exam_id" value="{{ $exam->id }}">
                                <input type="hidden" name="type" value="{{ $plan['key'] }}">
                                <input type="hidden" name="update_period" :value="period">
                                <button type="submit" class="w-full rounded-xl px-5 py-3.5 text-sm font-bold shadow-md transition hover:opacity-90 {{ !empty($plan['best']) ? 'text-white' : 'border border-slate-300 bg-white text-slate-900' }}" @if(!empty($plan['best'])) style="background: var(--brand);" @endif>Add to cart</button>
                            </form>
                        @else
                            <p class="mt-6 rounded-xl bg-slate-100 px-4 py-3 text-center text-sm font-semibold text-slate-600">Checkout is temporarily unavailable.</p>
                        @endif
                    </div>
                @endforeach
            </div>

            <div class="mx-auto mt-6 flex max-w-3xl flex-wrap items-center justify-center gap-x-6 gap-y-2 text-sm text-slate-600">
                <span class="flex items-center gap-2"><span style="color: var(--brand);">&#9679;</span> Access appears in your dashboard right after payment</span>
                @if($stripeEnabled)<span class="flex items-center gap-2"><span style="color: var(--brand);">&#9679;</span> Secure card payment</span>@endif
                @if($paypalEnabled)<span class="flex items-center gap-2"><span style="color: var(--brand);">&#9679;</span> PayPal accepted</span>@endif
            </div>
        </div>

        {{-- ============ TRY FIRST ============ --}}
        <div class="mx-auto flex max-w-3xl flex-col items-center gap-3 rounded-2xl border border-slate-200 bg-white p-6 text-center sm:flex-row sm:text-left">
            <div class="flex-1">
                <h2 class="text-lg font-extrabold text-slate-900">Not sure yet?</h2>
                <p class="text-sm text-slate-600">Answer {{ $freeCount ?: 'the' }} free {{ $code }} questions first, with explanations and no account.</p>
            </div>
            <a href="{{ $exam->questions_url }}" class="whitespace-nowrap rounded-xl px-5 py-3 text-sm font-bold text-white" style="background: var(--brand);">Try free questions</a>
        </div>

        {{-- ============ COMPARISON ============ --}}
        @if($hasPdf && $hasEngine)
        <section aria-labelledby="compare">
            <h2 id="compare" class="text-2xl font-extrabold text-slate-900">Compare the formats</h2>
            <div class="relative mt-5 overflow-x-auto rounded-2xl border border-slate-200 bg-white">
                <table class="w-full min-w-[560px] text-left text-sm">
                    <thead class="bg-slate-50 text-xs uppercase tracking-wider text-slate-500">
                        <tr><th class="px-5 py-3 font-bold">&nbsp;</th><th class="px-5 py-3 font-bold">PDF guide</th><th class="px-5 py-3 font-bold">Test engine</th><th class="px-5 py-3 font-bold">Bundle</th></tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-700">
                        @foreach([
                            ['Printable, works offline', true, false, true],
                            ['Timed exam mode', false, true, true],
                            ['Untimed practice mode', false, true, true],
                            ['Instant scoring', false, true, true],
                            ['Answers and explanations', true, true, true],
                            ['3 months of updates', true, true, true],
                        ] as [$row, $a, $b, $c])
                            <tr>
                                <th scope="row" class="px-5 py-3 font-semibold text-slate-900">{{ $row }}</th>
                                @foreach([$a, $b, $c] as $yes)
                                    <td class="px-5 py-3 {{ $yes ? 'font-bold' : 'text-slate-400' }}" @if($yes) style="color: var(--brand);" @endif>{!! $yes ? '&#10003; <span class="sr-only">Included</span>' : '&mdash; <span class="sr-only">Not included</span>' !!}</td>
                                @endforeach
                            </tr>
                        @endforeach
                        <tr class="bg-slate-50">
                            <th scope="row" class="px-5 py-3 font-semibold text-slate-900">Price (3 months)</th>
                            <td class="px-5 py-3 font-extrabold text-slate-900">{{ $money($prices['pdf'] + ($addons['pdf'][3] ?? 0)) }}</td>
                            <td class="px-5 py-3 font-extrabold text-slate-900">{{ $money($prices['engine_single'] + ($addons['engine_single'][3] ?? 0)) }}</td>
                            <td class="px-5 py-3 font-extrabold text-slate-900">{{ $hasBundle ? $money($prices['combo'] + ($addons['combo'][3] ?? 0)) : '-' }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>
        @endif

        {{-- ============ GUARANTEE / SUPPORT ============ --}}
        <section class="mx-auto max-w-3xl rounded-2xl border border-slate-200 bg-white p-6 text-center">
            @if(config('site.show_guarantee', true))
                <h2 class="text-lg font-extrabold text-slate-900">Our guarantee</h2>
                <p class="mt-1 text-sm text-slate-600">Read the full terms on our <a href="{{ url('/guarantee') }}" class="font-bold underline" style="color: var(--brand);">guarantee page</a>.</p>
            @else
                <h2 class="text-lg font-extrabold text-slate-900">Questions before you buy?</h2>
                <p class="mt-1 text-sm text-slate-600">We read every message. <a href="{{ url('/contact') }}" class="font-bold underline" style="color: var(--brand);">Contact support</a> and we will help you pick the right plan.</p>
            @endif
        </section>

        {{-- ============ FAQ ============ --}}
        <section aria-labelledby="faq" class="mx-auto w-full max-w-3xl">
            <h2 id="faq" class="text-2xl font-extrabold text-slate-900">Plan questions answered</h2>
            <div class="mt-5 space-y-3" x-data="{ open: 0 }">
                @foreach($faqs as $i => $faq)
                    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white">
                        <h3>
                            <button type="button" @click="open = open === {{ $i }} ? null : {{ $i }}" class="flex w-full items-center justify-between gap-4 px-5 py-4 text-left" :aria-expanded="open === {{ $i }}">
                                <span class="font-bold text-slate-900">{{ $faq['q'] }}</span>
                                <svg class="h-5 w-5 flex-none text-slate-400 transition" :class="open === {{ $i }} ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            </button>
                        </h3>
                        <div x-cloak :class="open === {{ $i }} ? '' : 'hidden'" class="border-t border-slate-100 px-5 py-4 text-sm leading-relaxed text-slate-600">{{ $faq['a'] }}</div>
                    </div>
                @endforeach
            </div>
        </section>

        <p class="mx-auto max-w-3xl text-center text-xs leading-relaxed text-slate-500">{{ $brand }} is independent and is not affiliated with, endorsed by or sponsored by {{ $vendorName }}.</p>
    </div>
</section>
@endsection
