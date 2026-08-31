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

<!-- Vendor Certifications Removed -->

<!-- Vendor Packages -->
@if(isset($vendorPackages) && count($vendorPackages) > 0)
<section class="py-12 bg-gray-50 border-b border-gray-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-10">
            <h2 class="text-3xl font-extrabold text-navy">{{ $vendor->name }} Access Packages</h2>
            <p class="text-gray-500 mt-2 max-w-2xl mx-auto">Get unlimited access to all {{ $vendor->name }} exams with our specially curated packages.</p>
        </div>

        @php
            $pkgCount = count($vendorPackages);
        @endphp
        
        @if($pkgCount === 1)
            @php $package = $vendorPackages->first(); @endphp
            <!-- Horizontal Premium Package Banner -->
            <div class="max-w-6xl mx-auto relative group rounded-[2rem] overflow-hidden p-[2px] transition-all duration-500 hover:shadow-[0_0_50px_rgba(0,212,170,0.25)]">
                <!-- Animated Gradient Border -->
                <div class="absolute inset-0 bg-gradient-to-r from-cyan via-blue-500 to-purple-600 opacity-70 group-hover:opacity-100 transition-opacity duration-500"></div>
                
                <div class="relative bg-[#0a1628] rounded-[30px] overflow-hidden flex flex-col lg:flex-row items-center justify-between z-10"
                     x-data="{
                        updatePeriod: '6',
                        licenseType: 'trainer',
                        basePrice: {{ (float)($package->type === 'subscription' ? $package->price_monthly : $package->price_lifetime) }},
                        extraUpdate3: {{ (float)($package->update_price_3_months ?? 0) }},
                        extraUpdate6: {{ (float)($package->update_price_6_months ?? 0) }},
                        extraUpdate12: {{ (float)($package->update_price_12_months ?? 0) }},
                        extraLicenseInd: {{ (float)($package->license_price_individual ?? 0) }},
                        extraLicenseCorp: {{ (float)($package->license_price_corporate ?? 0) }},
                        extraLicenseTrain: {{ (float)($package->license_price_trainer ?? 0) }},
                        get extraPrice() {
                            let extra = 0;
                            if (this.updatePeriod === '3') extra += this.extraUpdate3;
                            else if (this.updatePeriod === '6') extra += this.extraUpdate6;
                            else if (this.updatePeriod === '12') extra += this.extraUpdate12;

                            if (this.licenseType === 'individual') extra += this.extraLicenseInd;
                            else if (this.licenseType === 'corporate') extra += this.extraLicenseCorp;
                            else if (this.licenseType === 'trainer') extra += this.extraLicenseTrain;
                            
                            return extra;
                        }
                     }">
                    <!-- Subtle Glow -->
                    <div class="absolute top-0 left-0 w-96 h-96 bg-cyan/10 rounded-full blur-3xl -ml-20 -mt-20 group-hover:bg-cyan/20 transition-all duration-700"></div>
                    
                    <!-- Left Section (Info & Features) -->
                    <div class="p-8 md:p-12 lg:w-7/12 relative z-10 flex flex-col justify-center border-b lg:border-b-0 lg:border-r border-white/5">
                        @if($package->is_popular)
                            <div class="inline-flex mb-6">
                                <span class="bg-gradient-to-r from-orange to-red-500 text-white text-[11px] font-black px-4 py-1.5 rounded-full uppercase tracking-widest shadow-[0_0_15px_rgba(255,107,53,0.4)]">
                                    Ultimate Value
                                </span>
                            </div>
                        @endif
                        <h3 class="text-4xl lg:text-5xl font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-white to-gray-300 mb-6 leading-tight">{{ $package->name }}</h3>
                        <p class="text-base text-gray-400 leading-relaxed mb-10 max-w-xl">{{ $package->description }}</p>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            @foreach($package->features as $feature)
                                <div class="flex items-center space-x-3 bg-white/5 backdrop-blur-sm rounded-xl p-4 border border-white/5 hover:bg-white/10 transition-colors">
                                    <div class="shrink-0 w-8 h-8 rounded-full bg-gradient-to-br from-cyan to-blue-500 flex items-center justify-center shadow-[0_0_15px_rgba(0,212,170,0.3)]">
                                        <svg class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                                    </div>
                                    <span class="text-sm font-semibold text-gray-200">{{ $feature }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Right Section (Price & CTA) -->
                    <div class="p-8 md:p-12 lg:w-5/12 relative z-10 flex flex-col items-center justify-center bg-gradient-to-b from-[#0a1628] to-[#040b14]">
                        <div class="w-full max-w-sm">
                            <div class="text-center mb-8 pb-8 border-b border-white/5">
                                <div class="inline-block relative">
                                    <span class="text-6xl lg:text-7xl font-black text-transparent bg-clip-text bg-gradient-to-r from-cyan to-blue-400 block mb-3" x-text="`$${(basePrice + extraPrice).toFixed(2)}`">
                                        ${{ number_format(($package->type === 'subscription' ? $package->price_monthly : $package->price_lifetime) + ($package->update_price_6_months ?? 0) + ($package->license_price_trainer ?? 0), 2) }}
                                    </span>
                                </div>
                                <span class="text-gray-400 font-bold uppercase tracking-[0.2em] text-[11px] bg-white/5 py-2 px-4 rounded-full inline-block">
                                    {{ $package->type === 'subscription' ? 'Per Month' : ($package->access_days ? $package->access_days.' Days Access' : 'Lifetime Access') }}
                                </span>
                            </div>
                        
                        <form action="{{ url('/cart/add-package') }}" method="POST" class="w-full">
                            @csrf
                            <input type="hidden" name="package_id" value="{{ $package->id }}">
                            
                            <!-- Purchase Options UI -->
                            <div class="mb-7 space-y-5">
                                <!-- Product Updates -->
                                <div class="text-left">
                                    <div class="flex justify-between items-end mb-2">
                                        <label class="text-[10px] uppercase tracking-widest text-gray-400 font-bold">Product Updates</label>
                                        <span class="text-[10px] font-bold text-cyan tracking-wide" x-text="updatePeriod === '3' ? '+$' + extraUpdate3.toFixed(2) : (updatePeriod === '12' ? '+$' + extraUpdate12.toFixed(2) : 'Included')"></span>
                                    </div>
                                    <div class="flex p-1 space-x-1 bg-black/40 border border-white/10 rounded-xl relative backdrop-blur-md">
                                        <input type="hidden" name="update_period" x-model="updatePeriod">
                                        
                                        <button type="button" @click="updatePeriod = '3'" class="flex-1 py-2.5 text-xs font-semibold rounded-lg transition-all duration-300 relative z-10" :class="updatePeriod === '3' ? 'text-white' : 'text-gray-400 hover:text-gray-200'">
                                            3 Mo
                                        </button>
                                        <button type="button" @click="updatePeriod = '6'" class="flex-1 py-2.5 text-xs font-semibold rounded-lg transition-all duration-300 relative z-10" :class="updatePeriod === '6' ? 'text-white' : 'text-gray-400 hover:text-gray-200'">
                                            6 Mo
                                        </button>
                                        <button type="button" @click="updatePeriod = '12'" class="flex-1 py-2.5 text-xs font-semibold rounded-lg transition-all duration-300 relative z-10" :class="updatePeriod === '12' ? 'text-white' : 'text-gray-400 hover:text-gray-200'">
                                            12 Mo
                                        </button>
                                        
                                        <!-- Active Pill Background -->
                                        <div class="absolute top-1 bottom-1 left-1 w-[calc(33.333%-4px)] bg-gradient-to-r from-cyan to-blue-500 rounded-lg transition-all duration-300 ease-out shadow-[0_0_15px_rgba(0,212,170,0.3)]"
                                             :style="'transform: translateX(' + (updatePeriod === '3' ? '0' : (updatePeriod === '6' ? 'calc(100% + 4px)' : 'calc(200% + 8px)')) + ')'">
                                        </div>
                                    </div>
                                </div>

                                <!-- License Type -->
                                <div class="text-left">
                                    <div class="flex justify-between items-end mb-2">
                                        <label class="text-[10px] uppercase tracking-widest text-gray-400 font-bold">License Type</label>
                                        <span class="text-[10px] font-bold text-cyan tracking-wide" x-text="licenseType === 'individual' ? '2 PCs' : (licenseType === 'corporate' ? '10 PCs' : '25 PCs')"></span>
                                    </div>
                                    <div class="flex p-1 space-x-1 bg-black/40 border border-white/10 rounded-xl relative backdrop-blur-md">
                                        <input type="hidden" name="license_type" x-model="licenseType">
                                        
                                        <button type="button" @click="licenseType = 'individual'" class="flex-1 py-2.5 text-xs font-semibold rounded-lg transition-all duration-300 relative z-10" :class="licenseType === 'individual' ? 'text-white' : 'text-gray-400 hover:text-gray-200'">
                                            Personal
                                        </button>
                                        <button type="button" @click="licenseType = 'corporate'" class="flex-1 py-2.5 text-xs font-semibold rounded-lg transition-all duration-300 relative z-10" :class="licenseType === 'corporate' ? 'text-white' : 'text-gray-400 hover:text-gray-200'">
                                            Corp
                                        </button>
                                        <button type="button" @click="licenseType = 'trainer'" class="flex-1 py-2.5 text-xs font-semibold rounded-lg transition-all duration-300 relative z-10" :class="licenseType === 'trainer' ? 'text-white' : 'text-gray-400 hover:text-gray-200'">
                                            Trainer
                                        </button>
                                        
                                        <!-- Active Pill Background -->
                                        <div class="absolute top-1 bottom-1 left-1 w-[calc(33.333%-4px)] bg-gradient-to-r from-cyan to-blue-500 rounded-lg transition-all duration-300 ease-out shadow-[0_0_15px_rgba(0,212,170,0.3)]"
                                             :style="'transform: translateX(' + (licenseType === 'individual' ? '0' : (licenseType === 'corporate' ? 'calc(100% + 4px)' : 'calc(200% + 8px)')) + ')'">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <button type="submit" class="w-full flex items-center justify-center space-x-2 bg-gradient-to-r from-cyan to-blue-600 hover:from-blue-500 hover:to-purple-600 text-white shadow-[0_0_20px_rgba(0,212,170,0.4)] hover:shadow-[0_0_30px_rgba(59,130,246,0.6)] font-bold py-4 px-6 rounded-xl transition-all duration-300 transform active:scale-95">
                                <span class="text-sm tracking-wide uppercase">Get Full Access</span>
                                <svg class="w-5 h-5 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                            </button>
                        </form>
                        </div>
                    </div>
                </div>
            </div>
        @else
            <!-- Multi Package Grid -->
            <div class="{{ $pkgCount === 2 ? 'grid grid-cols-1 md:grid-cols-2 gap-8 max-w-4xl mx-auto' : 'grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 max-w-6xl mx-auto' }}">
                @foreach($vendorPackages as $package)
                    <div class="relative group rounded-3xl overflow-hidden p-[2px] transition-all duration-500 hover:-translate-y-2 hover:shadow-[0_0_50px_rgba(0,212,170,0.3)]">
                        <div class="absolute inset-0 bg-gradient-to-br from-cyan via-blue-500 to-purple-600 opacity-80 group-hover:opacity-100 transition-opacity duration-500"></div>
                        
                        <div class="relative h-full bg-[#0a1628] rounded-[22px] overflow-hidden flex flex-col z-10"
                             x-data="{
                                updatePeriod: '6',
                                licenseType: 'trainer',
                                basePrice: {{ (float)($package->type === 'subscription' ? $package->price_monthly : $package->price_lifetime) }},
                                extraUpdate3: {{ (float)($package->update_price_3_months ?? 0) }},
                                extraUpdate6: {{ (float)($package->update_price_6_months ?? 0) }},
                                extraUpdate12: {{ (float)($package->update_price_12_months ?? 0) }},
                                extraLicenseInd: {{ (float)($package->license_price_individual ?? 0) }},
                                extraLicenseCorp: {{ (float)($package->license_price_corporate ?? 0) }},
                                extraLicenseTrain: {{ (float)($package->license_price_trainer ?? 0) }},
                                get extraPrice() {
                                    let extra = 0;
                                    if (this.updatePeriod === '3') extra += this.extraUpdate3;
                                    else if (this.updatePeriod === '6') extra += this.extraUpdate6;
                                    else if (this.updatePeriod === '12') extra += this.extraUpdate12;
        
                                    if (this.licenseType === 'individual') extra += this.extraLicenseInd;
                                    else if (this.licenseType === 'corporate') extra += this.extraLicenseCorp;
                                    else if (this.licenseType === 'trainer') extra += this.extraLicenseTrain;
                                    
                                    return extra;
                                }
                             }">
                            <div class="absolute top-0 inset-x-0 h-1 bg-gradient-to-r from-cyan to-purple-500"></div>
                            @if($package->is_popular)
                                <div class="absolute top-6 right-6 bg-gradient-to-r from-orange to-red-500 text-white text-[10px] font-black px-3 py-1 rounded-full uppercase tracking-widest shadow-[0_0_15px_rgba(255,107,53,0.5)] z-20">
                                    Most Popular
                                </div>
                            @endif
                            <div class="p-8 md:p-10 flex-grow relative overflow-hidden">
                                <div class="absolute top-0 right-0 -mr-16 -mt-16 w-64 h-64 rounded-full bg-cyan/10 blur-3xl group-hover:bg-cyan/20 transition-all duration-500"></div>
                                <h3 class="relative text-3xl font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-white to-gray-300 mb-3 group-hover:from-cyan group-hover:to-blue-400 transition-all duration-300 pr-24">{{ $package->name }}</h3>
                                <p class="relative text-sm text-gray-400 mb-8 leading-relaxed min-h-[40px]">{{ $package->description }}</p>
                                <div class="relative mb-8 bg-white/5 backdrop-blur-sm inline-flex items-baseline px-6 py-4 rounded-2xl border border-white/10 shadow-inner">
                                    <span class="text-5xl font-black text-transparent bg-clip-text bg-gradient-to-r from-cyan to-blue-400" x-text="`$${(basePrice + extraPrice).toFixed(2)}`">${{ number_format(($package->type === 'subscription' ? $package->price_monthly : $package->price_lifetime) + ($package->update_price_6_months ?? 0) + ($package->license_price_trainer ?? 0), 2) }}</span>
                                    <span class="text-gray-400 ml-2 font-bold uppercase tracking-widest text-xs">{{ $package->type === 'subscription' ? '/ month' : ($package->access_days ? 'for '.$package->access_days.' days' : 'Lifetime Access') }}</span>
                                </div>
                                <ul class="relative space-y-4 mb-4">
                                    @foreach($package->features as $feature)
                                        <li class="flex items-start">
                                            <div class="shrink-0 w-7 h-7 rounded-full bg-gradient-to-br from-cyan to-blue-500 flex items-center justify-center mr-4 mt-0.5 shadow-[0_0_10px_rgba(0,212,170,0.4)]">
                                                <svg class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                                            </div>
                                            <span class="text-sm font-semibold text-gray-300 group-hover:text-white transition-colors duration-300">{{ $feature }}</span>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                            <div class="p-8 bg-white/5 backdrop-blur-md border-t border-white/10 relative z-10">
                                <form action="{{ url('/cart/add-package') }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="package_id" value="{{ $package->id }}">
                                    
                                    <!-- Purchase Options UI -->
                                    <div class="mb-6 space-y-4">
                                        <!-- Product Updates -->
                                        <div class="text-left">
                                            <div class="flex justify-between items-end mb-2">
                                                <label class="text-[10px] uppercase tracking-widest text-gray-400 font-bold">Product Updates</label>
                                                <span class="text-[9px] font-bold text-cyan tracking-wide" x-text="updatePeriod === '3' ? '+$' + extraUpdate3.toFixed(2) : (updatePeriod === '12' ? '+$' + extraUpdate12.toFixed(2) : 'Included')"></span>
                                            </div>
                                            <div class="flex p-1 space-x-1 bg-black/40 border border-white/10 rounded-xl relative backdrop-blur-md">
                                                <input type="hidden" name="update_period" x-model="updatePeriod">
                                                
                                                <button type="button" @click="updatePeriod = '3'" class="flex-1 py-2 text-xs font-semibold rounded-lg transition-all duration-300 relative z-10" :class="updatePeriod === '3' ? 'text-white' : 'text-gray-400 hover:text-gray-200'">
                                                    3 Mo
                                                </button>
                                                <button type="button" @click="updatePeriod = '6'" class="flex-1 py-2 text-xs font-semibold rounded-lg transition-all duration-300 relative z-10" :class="updatePeriod === '6' ? 'text-white' : 'text-gray-400 hover:text-gray-200'">
                                                    6 Mo
                                                </button>
                                                <button type="button" @click="updatePeriod = '12'" class="flex-1 py-2 text-xs font-semibold rounded-lg transition-all duration-300 relative z-10" :class="updatePeriod === '12' ? 'text-white' : 'text-gray-400 hover:text-gray-200'">
                                                    12 Mo
                                                </button>
                                                
                                                <!-- Active Pill Background -->
                                                <div class="absolute top-1 bottom-1 left-1 w-[calc(33.333%-4px)] bg-gradient-to-r from-cyan to-blue-500 rounded-lg transition-all duration-300 ease-out shadow-[0_0_15px_rgba(0,212,170,0.3)]"
                                                     :style="'transform: translateX(' + (updatePeriod === '3' ? '0' : (updatePeriod === '6' ? 'calc(100% + 4px)' : 'calc(200% + 8px)')) + ')'">
                                                </div>
                                            </div>
                                        </div>

                                        <!-- License Type -->
                                        <div class="text-left">
                                            <div class="flex justify-between items-end mb-2">
                                                <label class="text-[10px] uppercase tracking-widest text-gray-400 font-bold">License Type</label>
                                                <span class="text-[9px] font-bold text-cyan tracking-wide" x-text="licenseType === 'individual' ? '2 PCs' : (licenseType === 'corporate' ? '10 PCs' : '25 PCs')"></span>
                                            </div>
                                            <div class="flex p-1 space-x-1 bg-black/40 border border-white/10 rounded-xl relative backdrop-blur-md">
                                                <input type="hidden" name="license_type" x-model="licenseType">
                                                
                                                <button type="button" @click="licenseType = 'individual'" class="flex-1 py-2 text-xs font-semibold rounded-lg transition-all duration-300 relative z-10" :class="licenseType === 'individual' ? 'text-white' : 'text-gray-400 hover:text-gray-200'">
                                                    Personal
                                                </button>
                                                <button type="button" @click="licenseType = 'corporate'" class="flex-1 py-2 text-xs font-semibold rounded-lg transition-all duration-300 relative z-10" :class="licenseType === 'corporate' ? 'text-white' : 'text-gray-400 hover:text-gray-200'">
                                                    Corp
                                                </button>
                                                <button type="button" @click="licenseType = 'trainer'" class="flex-1 py-2 text-xs font-semibold rounded-lg transition-all duration-300 relative z-10" :class="licenseType === 'trainer' ? 'text-white' : 'text-gray-400 hover:text-gray-200'">
                                                    Trainer
                                                </button>
                                                
                                                <!-- Active Pill Background -->
                                                <div class="absolute top-1 bottom-1 left-1 w-[calc(33.333%-4px)] bg-gradient-to-r from-cyan to-blue-500 rounded-lg transition-all duration-300 ease-out shadow-[0_0_15px_rgba(0,212,170,0.3)]"
                                                     :style="'transform: translateX(' + (licenseType === 'individual' ? '0' : (licenseType === 'corporate' ? 'calc(100% + 4px)' : 'calc(200% + 8px)')) + ')'">
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <button type="submit" class="w-full flex items-center justify-center space-x-2 bg-gradient-to-r from-cyan to-blue-600 hover:from-blue-500 hover:to-purple-600 text-white shadow-[0_0_20px_rgba(0,212,170,0.4)] hover:shadow-[0_0_30px_rgba(59,130,246,0.6)] font-bold py-5 px-8 rounded-xl transition-all duration-300 transform active:scale-[0.98]">
                                        <span class="text-lg tracking-wide uppercase">Get {{ $package->name }}</span>
                                        <svg class="w-6 h-6 opacity-90 group-hover:animate-bounce" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</section>
@endif

<!-- Filter & Exams Grid -->
<section class="py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Toolbar Filters -->
        <div class="bg-white border border-gray-100 rounded-[20px] p-6 mb-8 flex flex-col md:flex-row justify-between items-center gap-6 shadow-[0_5px_20px_rgba(0,0,0,0.02)] relative z-10">
            <!-- Filter Form -->
            <form action="{{ url('/vendors/' . $vendor->slug) }}" method="GET" class="w-full flex flex-col sm:flex-row items-center gap-5">
                <div class="w-full sm:w-auto relative">
                    <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1.5 ml-1">Difficulty Level</label>
                    <select name="difficulty" onchange="this.form.submit()" class="w-full rounded-xl bg-gray-50 border border-gray-200 text-sm py-2.5 pl-4 pr-10 text-navy font-bold focus:outline-none focus:ring-2 focus:ring-cyan/20 focus:border-cyan transition-all appearance-none">
                        <option value="">All Levels</option>
                        <option value="Associate" {{ request('difficulty') === 'Associate' ? 'selected' : '' }}>Associate</option>
                        <option value="Professional" {{ request('difficulty') === 'Professional' ? 'selected' : '' }}>Professional</option>
                        <option value="Expert" {{ request('difficulty') === 'Expert' ? 'selected' : '' }}>Expert</option>
                    </select>
                    <!-- Custom select arrow -->
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 pt-5 text-gray-400">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"></path></svg>
                    </div>
                </div>
                
                <div class="w-full sm:w-auto relative">
                    <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1.5 ml-1">Sort By</label>
                    <select name="sort" onchange="this.form.submit()" class="w-full rounded-xl bg-gray-50 border border-gray-200 text-sm py-2.5 pl-4 pr-10 text-navy font-bold focus:outline-none focus:ring-2 focus:ring-cyan/20 focus:border-cyan transition-all appearance-none">
                        <option value="code" {{ request('sort') === 'code' ? 'selected' : '' }}>Exam Code</option>
                        <option value="price_low" {{ request('sort') === 'price_low' ? 'selected' : '' }}>Price: Low to High</option>
                        <option value="price_high" {{ request('sort') === 'price_high' ? 'selected' : '' }}>Price: High to Low</option>
                        <option value="updated" {{ request('sort') === 'updated' ? 'selected' : '' }}>Recently Updated</option>
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 pt-5 text-gray-400">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"></path></svg>
                    </div>
                </div>

                @if(request()->has('difficulty') || request()->has('sort'))
                    <a href="{{ url('/vendors/' . $vendor->slug) }}" class="text-[11px] text-red-500 hover:bg-red-50 px-3 py-1.5 rounded-lg mt-4 sm:mt-5 font-black uppercase tracking-widest transition-colors flex items-center">
                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path></svg>
                        Clear
                    </a>
                @endif
            </form>
        </div>

        <!-- Exams Listing -->
        @if(count($exams) > 0)
            <div class="grid grid-cols-1 gap-6">
                @foreach($exams as $exam)
                    <div class="bg-white border border-gray-100 rounded-[24px] p-6 sm:p-8 shadow-[0_10px_30px_rgba(0,0,0,0.02)] hover:shadow-[0_20px_50px_rgba(0,212,170,0.08)] hover:border-cyan/30 hover:-translate-y-1 transition-all duration-300 flex flex-col lg:flex-row items-start lg:items-center justify-between gap-6 relative overflow-hidden group">
                        <!-- Hover Glow -->
                        <div class="absolute inset-0 bg-gradient-to-r from-cyan/5 to-blue-500/5 opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none"></div>
                        <div class="absolute left-0 top-0 bottom-0 w-1.5 bg-gradient-to-b from-cyan to-blue-500 scale-y-0 group-hover:scale-y-100 transition-transform duration-300 origin-top"></div>
                        
                        <!-- Exam Info -->
                        <div class="flex-grow space-y-3 relative z-10">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="bg-navy/5 text-navy font-black text-[11px] uppercase tracking-widest px-3 py-1 rounded-lg border border-gray-200 group-hover:border-cyan/30 group-hover:bg-cyan/10 group-hover:text-cyan transition-colors">{{ $exam->exam_code }}</span>
                                <span class="bg-gray-50 text-gray-500 text-[10px] font-bold uppercase tracking-wider px-2.5 py-1 rounded-md border border-gray-100">{{ $exam->difficulty }}</span>
                                <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider flex items-center">
                                    <span class="w-1.5 h-1.5 rounded-full bg-cyan mr-1.5 animate-pulse"></span>
                                    Updated: {{ $exam->last_updated_at ? $exam->last_updated_at->format('M d, Y') : 'June 19, 2026' }}
                                </span>
                            </div>
                            <h2 class="text-xl sm:text-2xl font-black text-navy group-hover:text-cyan transition-colors leading-tight">
                                <a href="{{ url('/exams/' . $exam->slug) }}">{{ $exam->exam_name }}</a>
                            </h2>
                            <p class="text-sm text-gray-500 max-w-3xl line-clamp-2 leading-relaxed font-medium">{{ $exam->description }}</p>
                            <div class="flex items-center gap-4 text-[11px] text-gray-400 font-bold uppercase tracking-widest pt-2">
                                <span class="flex items-center bg-gray-50 px-2 py-1 rounded-md border border-gray-100">
                                    <svg class="w-4 h-4 mr-1 text-cyan" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    {{ $exam->question_count }} Q's
                                </span>
                                <span class="flex items-center bg-gray-50 px-2 py-1 rounded-md border border-gray-100">
                                    <svg class="w-4 h-4 mr-1 text-purple-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    {{ $exam->passing_score }}% Pass
                                </span>
                            </div>
                        </div>

                        <!-- Purchase & Actions Area -->
                        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-4 w-full lg:w-auto relative z-10 shrink-0">
                            <!-- Prices Info -->
                            <div class="flex lg:flex-col justify-between items-center lg:items-end lg:pr-5 min-w-32 border-b sm:border-b-0 lg:border-r border-gray-100 pb-4 sm:pb-0 lg:pb-0">
                                <div class="text-left lg:text-right">
                                    <span class="block text-[10px] text-gray-400 font-black uppercase tracking-widest mb-0.5">PDF Guide</span>
                                    <span class="text-2xl font-black text-navy leading-none group-hover:text-cyan transition-colors">${{ $exam->price_pdf }}</span>
                                </div>
                                <div class="text-right mt-0 lg:mt-3">
                                    <span class="block text-[9px] text-gray-400 font-black uppercase tracking-widest mb-0.5">Test Engine</span>
                                    <span class="text-sm font-black text-gray-500 leading-none">${{ $exam->price_engine }}</span>
                                </div>
                            </div>

                            <!-- CTA Buttons -->
                            <div class="flex flex-col gap-2.5 min-w-44">
                                <a href="{{ url('/exams/' . $exam->slug) }}" class="bg-gradient-to-r from-orange to-red-500 hover:from-red-500 hover:to-orange text-white text-[13px] font-black uppercase tracking-wider py-3 px-5 rounded-xl text-center shadow-[0_5px_15px_rgba(255,107,53,0.3)] hover:shadow-[0_8px_25px_rgba(255,107,53,0.4)] transition-all transform hover:-translate-y-0.5 flex items-center justify-center space-x-1">
                                    <span>View Options</span>
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"></path></svg>
                                </a>
                                <!-- Quick Buy PDF Form -->
                                <form action="{{ url('/cart/add') }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="exam_id" value="{{ $exam->id }}">
                                    <input type="hidden" name="type" value="pdf">
                                    <button type="submit" class="w-full border-2 border-gray-100 hover:border-cyan/50 hover:bg-cyan/5 text-navy hover:text-cyan text-[11px] font-black uppercase tracking-widest py-2.5 px-4 rounded-xl text-center transition-all flex items-center justify-center space-x-1">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                                        <span>Add PDF</span>
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
