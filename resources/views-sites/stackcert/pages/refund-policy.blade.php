{{--
    HOLDING PAGE. The owner has not supplied the refund policy yet. It must be published EXACTLY as the owner writes it,
    with nothing added. Replace the notice below with that text, then remove the noindex section if the page should rank.
--}}
@extends('layouts.public')

@php
    $brand = $currentSite ? $currentSite->name : config('app.name');
    $crumbs = [['name' => 'Home', 'url' => url('/')], ['name' => 'Refund policy', 'url' => url('/refund-policy')]];
@endphp

@section('title', "Refund Policy | {$brand}")
@section('meta_description', \App\Support\SeoMeta::staticPage('refund-policy', $brand)['description'])
@section('robots', 'noindex, follow')
@section('seo_tags')
    @include('partials.schema-breadcrumbs', ['crumbs' => $crumbs])
@endsection

@section('content')
@include('partials.static-hero', ['h1' => 'Refund policy', 'crumb' => 'Refund policy'])

<section class="py-10 lg:py-14">
    <div class="container-custom max-w-3xl text-base leading-relaxed text-slate-700">
        <p>Our refund policy has not been published yet. If you have a question about a purchase, please <a href="{{ url('/contact') }}" class="font-bold underline" style="color: var(--brand);">contact us</a> before you buy.</p>
    </div>
</section>
@endsection
