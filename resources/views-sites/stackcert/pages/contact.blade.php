@extends('layouts.public')

@php
    $brand = $currentSite ? $currentSite->name : config('app.name');
    $vendorName = \App\Models\Vendor::where('slug', (string) (config('site.vendors')[0] ?? ''))->value('name') ?: 'Microsoft';
    $email = ($currentSite && !empty($currentSite->contact_email)) ? $currentSite->contact_email : null;
    $crumbs = [['name' => 'Home', 'url' => url('/')], ['name' => 'Contact', 'url' => url('/contact')]];
    // One data source for the visible questions and the FAQPage markup
    $faqs = [
        ['q' => 'What should I include when I report a mistake in a question?', 'a' => 'The exam code, the question number and what you think is wrong. A link to the Microsoft Learn page that shows the correct answer helps us fix it faster.'],
        ['q' => 'Can I ask for an exam that is not on the site?', 'a' => 'Yes. Tell us the exam code and name. We add exams based on what candidates ask for, and we cannot promise a date.'],
        ['q' => 'What do I need to include for a billing or order question?', 'a' => 'The email address on your account, the exam code and the date of the order. Please never send a full card number.'],
        ['q' => 'Do you answer exam questions or give study advice?', 'a' => "We can point you to the right guide or the official study guide. For advice on whether an exam is right for your career, ask your employer, a mentor or {$vendorName}'s own certification pages."],
    ];
@endphp

@section('title', "Contact | {$brand}")
@section('meta_description', \App\Support\SeoMeta::staticPage('contact', $brand, $vendorName)['description'])
@section('seo_tags')
    @include('partials.schema-breadcrumbs', ['crumbs' => $crumbs])
    <script type="application/ld+json">
    {!! json_encode(\App\Support\SiteFaq::schema($faqs), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
    </script>
@endsection

@section('content')
@include('partials.static-hero', ['h1' => "Contact {$brand} support", 'crumb' => 'Contact', 'lead' => 'Questions about an order, a download, your account, or a question that looks wrong? Send us a message.'])

<section class="py-10 lg:py-14">
    <div class="container-custom grid gap-10 lg:grid-cols-12">
        <div class="lg:col-span-7">
            @if(session('success'))
                <div class="mb-6 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm font-semibold text-green-800" role="status">{{ session('success') }}</div>
            @endif
            @if($errors->any())
                <div class="mb-6 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-semibold text-red-700" role="alert">Please check the form: {{ $errors->first() }}</div>
            @endif
            <form action="{{ url('/contact') }}" method="POST" class="space-y-4 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                @csrf
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="c-name" class="block text-sm font-bold text-slate-800">Your name</label>
                        <input id="c-name" type="text" name="name" value="{{ old('name') }}" required autocomplete="name" class="mt-1 min-h-[48px] w-full rounded-xl border border-slate-300 px-4 text-base">
                    </div>
                    <div>
                        <label for="c-email" class="block text-sm font-bold text-slate-800">Your email</label>
                        <input id="c-email" type="email" name="email" value="{{ old('email') }}" required autocomplete="email" class="mt-1 min-h-[48px] w-full rounded-xl border border-slate-300 px-4 text-base">
                    </div>
                </div>
                <div>
                    <label for="c-subject" class="block text-sm font-bold text-slate-800">Subject</label>
                    <input id="c-subject" type="text" name="subject" value="{{ old('subject') }}" required placeholder="Order question, mistake in a question, exam request" class="mt-1 min-h-[48px] w-full rounded-xl border border-slate-300 px-4 text-base">
                </div>
                <div>
                    <label for="c-message" class="block text-sm font-bold text-slate-800">Message</label>
                    <textarea id="c-message" name="message" required rows="6" class="mt-1 w-full rounded-xl border border-slate-300 px-4 py-3 text-base">{{ old('message') }}</textarea>
                </div>
                <button type="submit" class="min-h-[48px] rounded-xl px-6 text-sm font-bold text-white" style="background: var(--brand);">Send message</button>
            </form>
        </div>

        <aside class="space-y-6 lg:col-span-5">
            <div class="rounded-2xl border border-slate-200 bg-white p-6">
                <h2 class="text-lg font-extrabold text-slate-900">What to contact us about</h2>
                <ul class="mt-3 list-disc space-y-1.5 pl-5 text-sm text-slate-600">
                    <li>Billing, orders and access to a purchase</li>
                    <li>PDF downloads and the test engine</li>
                    <li>A mistake in a question or an explanation</li>
                    <li>An exam you would like us to cover</li>
                </ul>
                <h2 class="mt-6 text-lg font-extrabold text-slate-900">Email and response time</h2>
                <p class="mt-2 text-sm text-slate-600">
                    @if($email)Write to <a href="mailto:{{ $email }}" class="font-bold underline" style="color: var(--brand);">{{ $email }}</a>.@endif
                    [RESPONSE TIME: owner to confirm, for example "within one business day"]
                </p>
            </div>
        </aside>
    </div>

    <div class="container-custom mt-12 max-w-3xl">
        <h2 class="text-2xl font-extrabold text-slate-900">Before you write</h2>
        <dl class="mt-5 space-y-4">
            @foreach($faqs as $faq)
                <div class="rounded-2xl border border-slate-200 bg-white p-5">
                    <dt class="font-bold text-slate-900">{{ $faq['q'] }}</dt>
                    <dd class="mt-1.5 text-sm leading-relaxed text-slate-600">{{ $faq['a'] }}</dd>
                </div>
            @endforeach
        </dl>
        <p class="mt-6 text-sm text-slate-600">More answers are on the <a href="{{ url('/faq') }}" class="font-bold underline" style="color: var(--brand);">FAQ page</a>.</p>
    </div>
</section>
@endsection
