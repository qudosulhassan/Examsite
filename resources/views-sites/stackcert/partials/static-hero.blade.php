{{-- Header band for fixed pages (about, contact, terms, ...). Needs $h1 and $crumb; optional $lead. --}}
<section class="border-b border-slate-200 bg-white">
    <div class="container-custom py-10 lg:py-14">
        <nav aria-label="Breadcrumb" class="text-sm text-slate-500">
            <ol class="flex flex-wrap items-center gap-x-2">
                <li><a href="{{ url('/') }}" class="hover:text-slate-900">Home</a></li>
                <li aria-hidden="true">/</li>
                <li aria-current="page" class="font-semibold text-slate-800">{{ $crumb }}</li>
            </ol>
        </nav>
        <h1 class="mt-4 text-3xl font-extrabold leading-tight text-slate-900 sm:text-4xl">{{ $h1 }}</h1>
        @isset($lead)
            <p class="mt-3 max-w-3xl text-base leading-relaxed text-slate-600 sm:text-lg">{{ $lead }}</p>
        @endisset
    </div>
</section>
