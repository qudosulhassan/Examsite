{{--
    DRAFT: owner to review before relying on it.
    Standard terms for digital study products. They have not been checked by a lawyer, and [JURISDICTION] must be filled in.
--}}
@extends('layouts.public')

@php
    $brand = $currentSite ? $currentSite->name : config('app.name');
    $vendorName = \App\Models\Vendor::where('slug', (string) (config('site.vendors')[0] ?? ''))->value('name') ?: 'Microsoft';
    $crumbs = [['name' => 'Home', 'url' => url('/')], ['name' => 'Terms of service', 'url' => url('/terms')]];
    $refundLink = '<a href="' . url('/refund-policy') . '" class="font-bold underline" style="color: var(--brand);">refund policy page</a>';
    $contactLink = '<a href="' . url('/contact') . '" class="font-bold underline" style="color: var(--brand);">contact page</a>';
    $sections = [
        ['Agreement', "These terms apply when you use {$brand}, including its free practice questions, PDF study guides and online test engine. By using the site or buying a plan you agree to them. If you do not agree, please do not use the site."],
        ['What we provide', "{$brand} provides study material for {$vendorName} certification exams: exam guides, practice questions with explanations, PDF study guides and an online test engine. It is study material only. It is not official exam content, and it does not guarantee that you will pass any exam."],
        ['Licence', "When you buy a plan we give you a personal, non-exclusive, non-transferable licence to use the purchased material for your own exam preparation. The licence is for one person."],
        ['What you may not do', "You may not share your account or your login with anyone else. You may not copy, republish, sell, rent, share or otherwise distribute the PDFs, questions, explanations or any other part of the material. You may not use automated tools to copy the site or to overload it. You may not use the site to break the rules of any certification programme."],
        ['Your account', "You are responsible for keeping your password secure and for what happens under your account. Tell us at once if you think someone else has used it. We may suspend an account that breaks these terms."],
        ['Payments and refunds', "Prices are shown on each exam's plans page before you pay. Our refund policy is on the {$refundLink}."],
        ["Independence from {$vendorName}", "{$brand} is not affiliated with, endorsed by or sponsored by {$vendorName}. Exam and product names belong to their owners and are used only to say which exam our material is for. Always check {$vendorName}'s official exam page and study guide for the current requirements before you book."],
        ['Our content', "We own, or have the right to use, the material on the site. Using the site does not give you any ownership of it, other than the licence above."],
        ['Limitation of liability', "We work to keep the material accurate and up to date, but we provide it as it is. To the extent the law allows, we are not responsible for exam results, for decisions you make based on the material, or for indirect or consequential losses. Nothing in these terms limits any right you have by law that cannot be limited."],
        ['Changes', "We may change these terms or the site from time to time. When we change the terms, the new version applies from the date shown on this page."],
        ['Governing law', "These terms are governed by the law of [JURISDICTION: owner to complete]."],
        ['Contact', "Questions about these terms? Use the {$contactLink}."],
    ];
@endphp

@section('title', "Terms of Service | {$brand}")
@section('meta_description', \App\Support\SeoMeta::staticPage('terms', $brand, $vendorName)['description'])
@section('seo_tags')
    @include('partials.schema-breadcrumbs', ['crumbs' => $crumbs])
@endsection

@section('content')
@include('partials.static-hero', ['h1' => 'Terms of service', 'crumb' => 'Terms of service', 'lead' => "The rules for using {$brand} and the study material you buy from it."])

<section class="py-10 lg:py-14">
    <div class="container-custom max-w-3xl space-y-8 text-base leading-relaxed text-slate-700">
        @foreach($sections as $i => [$heading, $text])
            <section>
                <h2 class="text-xl font-extrabold text-slate-900">{{ $i + 1 }}. {{ $heading }}</h2>
                <p class="mt-2">{!! $text !!}</p>
            </section>
        @endforeach
        <p class="text-sm text-slate-500">Last updated: [DATE: owner to set when these terms are approved]</p>
    </div>
</section>
@endsection
