{{-- StackCert footer: includes the required "independent of the vendor" trademark notice. --}}
@php
    $footBrand = $currentSite ? $currentSite->name : ($globalSettings['site_name'] ?? config('app.name'));
    $footVendorSlug = (count((array) config('site.vendors', [])) === 1) ? config('site.vendors')[0] : null;
    $footVendor = $footVendorSlug ? \App\Models\Vendor::where('slug', $footVendorSlug)->first() : null;
    $footVendorName = $footVendor?->name ?? 'IT vendor';
    $footSupport = $globalSettings['support_email'] ?? $globalSettings['contact_email'] ?? null;
    // Dark footer: the reversed (white-text) version of the site logo, when the site has one.
    $footLogo = ($currentSite && !empty($currentSite->logo) && is_file(public_path(str_replace('primary', 'reversed', $currentSite->logo))))
        ? asset(str_replace('primary', 'reversed', $currentSite->logo)) : null;
@endphp

<footer class="mt-auto bg-slate-950 text-slate-300">
    <div class="container-custom py-14">
        <div class="grid grid-cols-2 gap-10 md:grid-cols-12">
            <div class="col-span-2 md:col-span-5 space-y-4">
                <a href="{{ url('/') }}" class="inline-flex items-center gap-2.5" aria-label="{{ $footBrand }} home">
                    @if($footLogo)
                        <img src="{{ $footLogo }}" alt="{{ $footBrand }} logo" class="h-7 w-auto max-w-[260px]" width="260" height="29" loading="lazy">
                    @else
                        <span class="flex h-9 w-9 items-center justify-center rounded-xl text-base font-black text-white" style="background: linear-gradient(135deg, var(--brand), var(--brand-dark));">{{ strtoupper(mb_substr($footBrand, 0, 1)) }}</span>
                    <span class="text-lg font-extrabold tracking-tight text-white">{{ $footBrand }}</span>
                    @endif
                </a>
                <p class="max-w-sm text-sm leading-relaxed text-slate-400">
                    Practice questions, study guides and a timed test engine for {{ $footVendorName }} certification exams, kept up to date as the exams change.
                </p>
                @if($footSupport)
                    <p class="text-sm"><a href="mailto:{{ $footSupport }}" class="font-semibold text-white hover:underline">{{ $footSupport }}</a></p>
                @endif
            </div>

            <div class="md:col-span-2 space-y-3">
                <p class="text-xs font-bold uppercase tracking-widest text-slate-500">Learn</p>
                <ul class="space-y-2 text-sm">
                    <li><a href="{{ $footVendorSlug ? url('/vendors/' . $footVendorSlug) : url('/vendors') }}" class="hover:text-white">All exams</a></li>
                    <li><a href="{{ url('/') }}#tracks" class="hover:text-white">Exam tracks</a></li>
                    <li><a href="{{ url('/certifications') }}" class="hover:text-white">Certifications</a></li>
                    <li><a href="{{ url('/test-engine') }}" class="hover:text-white">Test engine</a></li>
                    <li><a href="{{ url('/blog') }}" class="hover:text-white">Blog</a></li>
                </ul>
            </div>

            <div class="md:col-span-2 space-y-3">
                <p class="text-xs font-bold uppercase tracking-widest text-slate-500">Support</p>
                <ul class="space-y-2 text-sm">
                    <li><a href="{{ url('/faq') }}" class="hover:text-white">FAQ</a></li>
                    <li><a href="{{ url('/contact') }}" class="hover:text-white">Contact</a></li>
                    <li><a href="{{ url('/about') }}" class="hover:text-white">About</a></li>
                    <li><a href="{{ url('/free-demo') }}" class="hover:text-white">Free sample</a></li>
                </ul>
            </div>

            <div class="md:col-span-3 space-y-3">
                <p class="text-xs font-bold uppercase tracking-widest text-slate-500">Legal</p>
                <ul class="space-y-2 text-sm">
                    <li><a href="{{ url('/privacy-policy') }}" class="hover:text-white">Privacy policy</a></li>
                    @if(\Illuminate\Support\Facades\Route::has('terms'))
                        <li><a href="{{ route('terms') }}" class="hover:text-white">Terms of service</a></li>
                    @endif
                    <li><a href="{{ url('/dmca') }}" class="hover:text-white">DMCA &amp; copyright</a></li>
                </ul>
            </div>
        </div>

        <div class="mt-12 border-t border-white/10 pt-6 space-y-3 text-xs leading-relaxed text-slate-500">
            <p>&copy; {{ date('Y') }} {{ $footBrand }}. All rights reserved.</p>
            <p>
                {{ $footBrand }} is an independent exam-preparation provider. It is not affiliated with, endorsed by or sponsored by
                {{ $footVendorName }}. @if($footVendorName === 'Microsoft')Microsoft, Azure, Microsoft 365, Dynamics 365, Power Platform and other product and certification names are trademarks of the Microsoft group of companies.@else All product, company and certification names are trademarks of their respective owners.@endif
                Practice materials on this site are not produced or approved by the exam vendor and are not official exam content.
            </p>
        </div>
    </div>
</footer>
