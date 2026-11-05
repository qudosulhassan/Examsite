@extends('layouts.public')

@php
    $brand = $currentSite ? $currentSite->name : config('app.name');
    $vendorName = \App\Models\Vendor::where('slug', (string) (config('site.vendors')[0] ?? ''))->value('name') ?: 'Microsoft';
    $crumbs = [['name' => 'Home', 'url' => url('/')], ['name' => 'About', 'url' => url('/about')]];
    $editor = config('site.editorial');
@endphp

@section('title', "About | {$brand}")
@section('meta_description', \App\Support\SeoMeta::staticPage('about', $brand, $vendorName)['description'])
@section('seo_tags')
    @include('partials.schema-breadcrumbs', ['crumbs' => $crumbs])
@endsection

@section('content')
@include('partials.static-hero', ['h1' => "About {$brand}", 'crumb' => 'About', 'lead' => "An independent site for {$vendorName} certification exam preparation: exam guides, practice questions with explanations and a timed test engine."])

<section class="py-10 lg:py-14">
    <div class="container-custom max-w-3xl space-y-10 text-base leading-relaxed text-slate-700">

        <section aria-labelledby="what">
            <h2 id="what" class="text-2xl font-extrabold text-slate-900">What {{ $brand }} is</h2>
            <p class="mt-3">{{ $brand }} helps people prepare for {{ $vendorName }} certification exams. For every exam we cover there is a guide that follows {{ $vendorName }}'s own skills outline, a page of free practice questions with an explanation for each answer, and plans that add a larger question bank, a timed online test engine and a PDF study guide.</p>
            <p class="mt-3">We are a study site, not an exam provider. We do not run the exams, we do not issue certifications, and nothing here changes what {{ $vendorName }} asks on the day.</p>
        </section>

        <section aria-labelledby="why">
            <h2 id="why" class="text-2xl font-extrabold text-slate-900">Why it exists</h2>
            <p class="mt-3">{{ $vendorName }} exams test whether you can do the work, not whether you can repeat a list of answers. People who prepare well tend to do three things: they learn the skills Microsoft lists, they check their understanding with questions that explain why an answer is right, and they practise under time pressure before the real exam. {{ $brand }} is built around those three steps.</p>
            <p class="mt-3">[WHY THE SITE WAS STARTED: one or two sentences from the owner]</p>
        </section>

        <section aria-labelledby="guides">
            <h2 id="guides" class="text-2xl font-extrabold text-slate-900">How the exam guides are built</h2>
            <p class="mt-3">Each guide is checked against the official study guide for that exam on Microsoft Learn. We take the domains, their weightings, the listed skills and the date {{ $vendorName }} last updated the outline, and we add a study plan and FAQs that help you use them. Every guide links to the official page, and shows the date we checked it so you can see how current it is.</p>
            <p class="mt-3">When {{ $vendorName }} updates an outline, changes the weightings or retires an exam, we update the guide to match. Where an exam is retiring or has been retired, the guide says so at the top and the page is kept out of search results.</p>
        </section>

        <section aria-labelledby="questions">
            <h2 id="questions" class="text-2xl font-extrabold text-slate-900">How questions and explanations are written and reviewed</h2>
            <p class="mt-3">[DESCRIBE HOW PRACTICE QUESTIONS AND EXPLANATIONS ARE WRITTEN, WHO REVIEWS THEM AND HOW OFTEN: owner to complete]</p>
            <p class="mt-3">Whatever the process, our practice questions are study material. They are not official exam content, and we do not publish real exam questions or "braindumps". {{ $vendorName }} prohibits sharing real exam content, and memorised answers do not show that you can do the job.</p>
        </section>

        <section aria-labelledby="errors">
            <h2 id="errors" class="text-2xl font-extrabold text-slate-900">Found a mistake?</h2>
            <p class="mt-3">Tell us. Send the exam code, the question number and what you think is wrong through the <a href="{{ url('/contact') }}" class="font-bold underline" style="color: var(--brand);">contact page</a>. We check the point against the official documentation and correct the question or explanation when it is a mistake. Accurate answers matter more to us than looking perfect.</p>
        </section>

        <section aria-labelledby="independence">
            <h2 id="independence" class="text-2xl font-extrabold text-slate-900">Independence</h2>
            <p class="mt-3">{{ $brand }} is an independent exam-preparation provider. It is not affiliated with, endorsed by or sponsored by {{ $vendorName }}. @if($vendorName === 'Microsoft')Microsoft, Azure, Microsoft 365, Dynamics 365, Power Platform and the names of its certifications and exams are trademarks of the Microsoft group of companies. @endif We use them only to say which exam our material is for.</p>
        </section>

        <section aria-labelledby="people">
            <h2 id="people" class="text-2xl font-extrabold text-slate-900">Who is behind it</h2>
            @if(!empty($editor['name']))
                <p class="mt-3">{{ $editor['name'] }}@if(!empty($editor['role'])), {{ $editor['role'] }}@endif. {{ $editor['bio'] ?? '' }}</p>
            @else
                <p class="mt-3">[FOUNDER NAME]: [ROLE AND BACKGROUND IN ONE OR TWO SENTENCES]</p>
                <p class="mt-3">[YEARS OF EXPERIENCE / CERTIFICATIONS HELD]</p>
            @endif
        </section>

        <p class="rounded-2xl border border-slate-200 bg-slate-50 p-5 text-sm text-slate-600">
            Ready to start? Browse the <a href="{{ url('/vendors/' . (config('site.vendors')[0] ?? 'microsoft')) }}" class="font-bold underline" style="color: var(--brand);">{{ $vendorName }} exams</a>, read the <a href="{{ url('/faq') }}" class="font-bold underline" style="color: var(--brand);">FAQ</a> or <a href="{{ url('/contact') }}" class="font-bold underline" style="color: var(--brand);">get in touch</a>.
        </p>
    </div>
</section>
@endsection
