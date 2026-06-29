@extends('layouts.public')

@section('title', $vendor->meta_title ?? "{$vendor->name} Certification Exam Study Guides - ExamsNinja")
@section('meta_description', $vendor->meta_description ?? "Browse our extensive catalog of updated {$vendor->name} certification exams, study guides, and verified question banks.")
@section('meta_keywords', $vendor->meta_keywords ?? "{$vendor->name} exams, {$vendor->name} certification, {$vendor->name} practice test, {$vendor->name} dumps")
@section('canonical_url', route('vendors.show', $vendor->slug))
@section('og_type', 'website')

@section('seo_tags')
<script type="application/ld+json">
{
  "@@context": "https://schema.org",
  "@type": "Organization",
  "name": "{{ $vendor->name }}",
  "description": "{{ strip_tags($vendor->description) }}",
  "url": "{{ route('vendors.show', $vendor->slug) }}"
}
</script>
@endsection

@section('content')
<!-- Vendor Header -->
<section class="bg-navy text-white py-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Breadcrumbs -->
        <div class="mb-6">
            <x-breadcrumbs :links="[
                ['name' => 'Home', 'url' => '/'],
                ['name' => 'Vendors', 'url' => '/vendors'],
                ['name' => $vendor->name, 'url' => '']
            ]" />
        </div>

        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div class="flex items-center space-x-5">
                @php
                    $logoStyles = [
                        'microsoft' => [
                            'bg' => 'bg-slate-50 border-gray-200', 
                            'html' => '<div class="grid grid-cols-2 gap-0.5 w-8 h-8"><div class="bg-red-500 w-3.5 h-3.5"></div><div class="bg-green-500 w-3.5 h-3.5"></div><div class="bg-blue-500 w-3.5 h-3.5"></div><div class="bg-yellow-500 w-3.5 h-3.5"></div></div>'
                        ],
                        'amazon-web-services-aws' => [
                            'bg' => 'bg-zinc-900 border-zinc-700', 
                            'html' => '<div class="flex flex-col items-center justify-center"><span class="text-xs tracking-widest font-extrabold text-white leading-none">AWS</span><svg class="w-8 h-2.5 text-amber-500 -mt-0.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 8"><path stroke-linecap="round" d="M2 2c6 4 14 4 20 0m-2.5 1.5L22 2l-3.5-1" /></svg></div>'
                        ],
                        'google-cloud-platform-gcp' => [
                            'bg' => 'bg-white border-gray-150', 
                            'html' => '<div class="flex items-center space-x-0.5"><span class="text-blue-500 font-extrabold text-lg">G</span><span class="text-red-500 font-extrabold text-lg">C</span><span class="text-yellow-500 font-extrabold text-lg">P</span></div>'
                        ],
                        'cisco' => [
                            'bg' => 'bg-sky-50 border-sky-200', 
                            'html' => '<div class="flex items-end justify-center space-x-0.5 h-8"><div class="bg-sky-650 w-0.5 h-3 rounded-full"></div><div class="bg-sky-650 w-0.5 h-4.5 rounded-full"></div><div class="bg-sky-650 w-0.5 h-6 rounded-full"></div><div class="bg-sky-650 w-0.5 h-4.5 rounded-full"></div><div class="bg-sky-650 w-0.5 h-3 rounded-full"></div></div>'
                        ],
                        'comptia' => [
                            'bg' => 'bg-emerald-50 border-emerald-250', 
                            'html' => '<span class="text-sm font-black tracking-tighter text-emerald-700">CompTIA</span>'
                        ],
                        'salesforce' => [
                            'bg' => 'bg-sky-50 border-sky-100', 
                            'html' => '<svg class="w-9 h-9 text-sky-500" fill="currentColor" viewBox="0 0 24 24"><path d="M19.35 10.04C18.67 6.59 15.64 4 12 4 9.11 4 6.6 5.64 5.35 8.04 2.34 8.36 0 10.91 0 14c0 3.31 2.69 6 6 6h13c2.76 0 5-2.24 5-5 0-2.64-2.05-4.78-4.65-4.96z"/></svg>'
                        ],
                        'oracle' => [
                            'bg' => 'bg-red-50 border-red-150', 
                            'html' => '<span class="text-sm font-serif font-black tracking-tight uppercase text-red-600">Oracle</span>'
                        ],
                        'red-hat' => [
                            'bg' => 'bg-red-950 border-red-900', 
                            'html' => '<span class="text-xs font-bold font-sans text-white">RedHat</span>'
                        ],
                        'vmware' => [
                            'bg' => 'bg-slate-50 border-teal-250', 
                            'html' => '<span class="text-sm font-bold tracking-tight text-teal-600 font-mono">vmware</span>'
                        ],
                        'project-management-institute-pmi' => [
                            'bg' => 'bg-purple-50 border-purple-200', 
                            'html' => '<span class="text-sm font-extrabold tracking-tighter text-purple-700">PMI</span>'
                        ],
                        'isaca' => [
                            'bg' => 'bg-indigo-50 border-indigo-200', 
                            'html' => '<span class="text-sm font-extrabold tracking-tighter uppercase text-indigo-700">ISACA</span>'
                        ],
                        'itil' => [
                            'bg' => 'bg-green-50 border-green-200', 
                            'html' => '<span class="text-sm font-extrabold font-mono tracking-tighter text-green-700">ITIL</span>'
                        ],
                        'palo-alto' => [
                            'bg' => 'bg-orange-50 border-orange-200', 
                            'html' => '<span class="text-xs font-black tracking-tighter uppercase text-orange-700">PaloAlto</span>'
                        ],
                        'fortinet' => [
                            'bg' => 'bg-red-50 border-red-200', 
                            'html' => '<span class="text-sm font-black uppercase text-red-700">Forti</span>'
                        ]
                    ];
                    $style = $logoStyles[$vendor->slug] ?? [
                        'bg' => 'bg-white bg-opacity-10 border border-gray-700', 
                        'html' => '<span class="text-white font-bold text-2xl uppercase">' . substr($vendor->name, 0, 2) . '</span>'
                    ];
                @endphp
                @if($vendor->logo_path)
                    <div class="h-16 w-16 rounded-lg flex items-center justify-center bg-white border-gray-200 p-2">
                        <img src="{{ $vendor->logo_path }}" alt="{{ $vendor->name }}" class="max-h-full max-w-full object-contain">
                    </div>
                @else
                    <div class="h-16 w-16 rounded-lg flex items-center justify-center border {{ $style['bg'] }}">
                        {!! $style['html'] !!}
                    </div>
                @endif
                <div>
                    <h1 class="text-3xl font-extrabold tracking-tight mb-2">{{ $vendor->name }} Certification Study Guides</h1>
                    <p class="text-sm text-gray-300 max-w-xl">{{ $vendor->description }}</p>
                </div>
            </div>
            <div class="bg-gray-800 border border-gray-700 rounded-lg px-6 py-4 flex flex-col items-center justify-center text-center">
                <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Total Active Guides</span>
                <span class="text-3xl font-bold text-cyan mt-1">{{ count($exams) }}</span>
            </div>
        </div>
    </div>
</section>

<!-- Vendor Packages -->
@if(isset($vendorPackages) && count($vendorPackages) > 0)
<section class="py-12 bg-gray-50 border-b border-gray-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-10">
            <h2 class="text-3xl font-extrabold text-navy">{{ $vendor->name }} Access Packages</h2>
            <p class="text-gray-500 mt-2 max-w-2xl mx-auto">Get unlimited access to all {{ $vendor->name }} exams with our specially curated packages.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 max-w-5xl mx-auto">
            @foreach($vendorPackages as $package)
                <div class="bg-white rounded-2xl border {{ $package->is_popular ? 'border-orange shadow-orange/10' : 'border-gray-200 shadow-sm' }} overflow-hidden flex flex-col relative transition-transform hover:-translate-y-1 hover:shadow-lg">
                    @if($package->is_popular)
                        <div class="absolute top-0 right-0 bg-orange text-white text-xs font-bold px-3 py-1 rounded-bl-lg uppercase tracking-wide">
                            Most Popular
                        </div>
                    @endif
                    
                    <div class="p-6 md:p-8 border-b border-gray-100 flex-grow">
                        <h3 class="text-xl font-bold text-navy mb-2">{{ $package->name }}</h3>
                        <p class="text-sm text-gray-500 mb-6 min-h-[40px]">{{ $package->description }}</p>
                        
                        <div class="flex items-baseline mb-6">
                            <span class="text-4xl font-extrabold text-gray-900">
                                ${{ $package->type === 'subscription' ? $package->price_monthly : $package->price_lifetime }}
                            </span>
                            <span class="text-gray-500 ml-2 font-medium">
                                {{ $package->type === 'subscription' ? '/mo' : ($package->access_days ? 'for '.$package->access_days.' days' : 'lifetime') }}
                            </span>
                        </div>

                        <ul class="space-y-4 mb-8">
                            @foreach($package->features as $feature)
                                <li class="flex items-start">
                                    <svg class="h-5 w-5 text-cyan shrink-0 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                    <span class="text-sm text-gray-600">{{ $feature }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>

                    <div class="p-6 md:p-8 bg-gray-50">
                        <form action="{{ url('/cart/add-package') }}" method="POST">
                            @csrf
                            <input type="hidden" name="package_id" value="{{ $package->id }}">
                            <button type="submit" class="w-full block text-center {{ $package->is_popular ? 'bg-cyan hover:bg-navy text-white shadow-cyan/30' : 'bg-white hover:bg-gray-50 text-navy border-2 border-gray-200' }} font-bold py-3 px-4 rounded-xl shadow-sm transition duration-200">
                                Get {{ $package->name }}
                            </button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
@endif

<!-- Filter & Exams Grid -->
<section class="py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Toolbar Filters -->
        <div class="bg-white border border-gray-200 rounded-lg p-4 mb-8 flex flex-col md:flex-row justify-between items-center gap-4">
            <!-- Filter Form -->
            <form action="{{ url('/vendors/' . $vendor->slug) }}" method="GET" class="w-full flex flex-col sm:flex-row items-center gap-4">
                <div class="w-full sm:w-auto">
                    <label class="block text-xs font-bold text-navy uppercase mb-1">Difficulty Level</label>
                    <select name="difficulty" onchange="this.form.submit()" class="w-full rounded bg-gray-50 border border-gray-200 text-sm py-2 px-3 text-gray-700 focus:outline-none focus:ring-1 focus:ring-cyan">
                        <option value="">All Levels</option>
                        <option value="Associate" {{ request('difficulty') === 'Associate' ? 'selected' : '' }}>Associate</option>
                        <option value="Professional" {{ request('difficulty') === 'Professional' ? 'selected' : '' }}>Professional</option>
                        <option value="Expert" {{ request('difficulty') === 'Expert' ? 'selected' : '' }}>Expert</option>
                    </select>
                </div>

                <div class="w-full sm:w-auto">
                    <label class="block text-xs font-bold text-navy uppercase mb-1">Sort By</label>
                    <select name="sort" onchange="this.form.submit()" class="w-full rounded bg-gray-50 border border-gray-200 text-sm py-2 px-3 text-gray-700 focus:outline-none focus:ring-1 focus:ring-cyan">
                        <option value="code" {{ request('sort') === 'code' ? 'selected' : '' }}>Exam Code</option>
                        <option value="price_low" {{ request('sort') === 'price_low' ? 'selected' : '' }}>Price: Low to High</option>
                        <option value="price_high" {{ request('sort') === 'price_high' ? 'selected' : '' }}>Price: High to Low</option>
                        <option value="updated" {{ request('sort') === 'updated' ? 'selected' : '' }}>Recently Updated</option>
                    </select>
                </div>

                @if(request()->has('difficulty') || request()->has('sort'))
                    <a href="{{ url('/vendors/' . $vendor->slug) }}" class="text-xs text-red-500 hover:underline mt-4 sm:mt-0 font-medium">Clear Filters</a>
                @endif
            </form>
        </div>

        <!-- Exams Listing -->
        @if(count($exams) > 0)
            <div class="grid grid-cols-1 gap-6">
                @foreach($exams as $exam)
                    <div class="bg-white border border-gray-200 rounded-lg p-6 hover:shadow-md transition duration-200 flex flex-col lg:flex-row items-start lg:items-center justify-between gap-6">
                        <!-- Exam Info -->
                        <div class="flex-grow space-y-2">
                            <div class="flex items-center space-x-2">
                                <span class="bg-cyan bg-opacity-15 text-navy font-bold text-xs px-2.5 py-0.5 rounded border border-cyan border-opacity-30">{{ $exam->exam_code }}</span>
                                <span class="bg-gray-100 text-gray-600 text-xs px-2 py-0.5 rounded">{{ $exam->difficulty }}</span>
                                <span class="text-xs text-gray-400">Updated: {{ $exam->last_updated_at ? $exam->last_updated_at->format('M d, Y') : 'June 19, 2026' }}</span>
                            </div>
                            <h2 class="text-lg font-bold text-navy hover:text-cyan transition">
                                <a href="{{ url('/exams/' . $exam->slug) }}">{{ $exam->exam_name }}</a>
                            </h2>
                            <p class="text-xs text-gray-500 max-w-2xl line-clamp-2">{{ $exam->description }}</p>
                            <div class="flex items-center space-x-4 text-xs text-gray-400 font-semibold pt-1">
                                <span>❓ {{ $exam->question_count }} Questions</span>
                                <span>🎯 {{ $exam->passing_score }}% Passing Score</span>
                            </div>
                        </div>

                        <!-- Purchase & Actions Area -->
                        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 w-full lg:w-auto">
                            <!-- Prices Info -->
                            <div class="flex lg:flex-col justify-between items-center lg:items-end lg:pr-4 min-w-32 border-b sm:border-b-0 lg:border-r border-gray-150 pb-2 sm:pb-0 lg:pb-0">
                                <div>
                                    <span class="block text-[10px] text-gray-400 font-bold uppercase tracking-wider">PDF Guide</span>
                                    <span class="text-base font-bold text-navy">${{ $exam->price_pdf }}</span>
                                </div>
                                <div class="lg:mt-2">
                                    <span class="block text-[10px] text-gray-400 font-bold uppercase tracking-wider">Test Engine</span>
                                    <span class="text-sm font-semibold text-gray-500">${{ $exam->price_engine }}</span>
                                </div>
                            </div>

                            <!-- CTA Buttons -->
                            <div class="flex flex-col gap-2 min-w-44">
                                <a href="{{ url('/exams/' . $exam->slug) }}" class="bg-orange hover-bg-orange text-white text-xs font-bold py-2.5 px-4 rounded text-center shadow-sm transition">
                                    View Options & Demo
                                </a>
                                <!-- Quick Buy PDF Form -->
                                <form action="{{ url('/cart/add') }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="exam_id" value="{{ $exam->id }}">
                                    <input type="hidden" name="type" value="pdf">
                                    <button type="submit" class="w-full border border-gray-300 hover:border-cyan hover:bg-cyan hover:text-navy text-navy text-xs font-bold py-2 px-4 rounded text-center transition">
                                        Add PDF to Cart
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="bg-white border border-gray-200 rounded-lg p-16 text-center text-gray-500">
                <p class="text-lg font-semibold">No study guides found matching the selected filters.</p>
                <a href="{{ url('/vendors/' . $vendor->slug) }}" class="text-sm text-cyan hover:underline mt-2 inline-block">Clear All Filters</a>
            </div>
        @endif
    </div>
</section>
@endsection
