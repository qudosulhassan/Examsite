@extends('layouts.public')

@section('title', 'Browse IT Certification Providers - ExamsNinja')

@section('content')
<!-- Header Banner -->
<section class="bg-navy text-white py-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h1 class="text-3xl sm:text-4xl font-extrabold tracking-tight mb-4">
            Browse IT Certification Providers
        </h1>
        <p class="text-lg text-gray-300 max-w-2xl mx-auto">
            Choose your exam vendor below to find updated study guides, verified question banks, and dynamic test engines.
        </p>
    </div>
</section>

<!-- Vendor Grid -->
<section class="py-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Vendors Cards Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            @foreach($vendors as $vendor)
                <div class="group bg-white border border-gray-200 rounded-lg p-6 flex flex-col justify-between hover:shadow-lg hover:border-cyan transition duration-300">
                    <div class="flex items-center space-x-4 mb-4">
                        @php
                            $logoStyles = [
                                'microsoft' => [
                                    'bg' => 'bg-slate-50 border-gray-200', 
                                    'html' => '<div class="grid grid-cols-2 gap-0.5 w-6 h-6"><div class="bg-red-500 w-2.5 h-2.5"></div><div class="bg-green-500 w-2.5 h-2.5"></div><div class="bg-blue-500 w-2.5 h-2.5"></div><div class="bg-yellow-500 w-2.5 h-2.5"></div></div>'
                                ],
                                'amazon-web-services-aws' => [
                                    'bg' => 'bg-zinc-900 border-zinc-700', 
                                    'html' => '<div class="flex flex-col items-center justify-center"><span class="text-[10px] tracking-widest font-extrabold text-white leading-none">AWS</span><svg class="w-6 h-2 text-amber-500 -mt-0.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 8"><path stroke-linecap="round" d="M2 2c6 4 14 4 20 0m-2.5 1.5L22 2l-3.5-1" /></svg></div>'
                                ],
                                'google-cloud-platform-gcp' => [
                                    'bg' => 'bg-white border-gray-150', 
                                    'html' => '<div class="flex items-center space-x-0.5"><span class="text-blue-500 font-extrabold text-sm">G</span><span class="text-red-500 font-extrabold text-sm">C</span><span class="text-yellow-500 font-extrabold text-sm">P</span></div>'
                                ],
                                'cisco' => [
                                    'bg' => 'bg-sky-50 border-sky-200', 
                                    'html' => '<div class="flex items-end justify-center space-x-0.5 h-6"><div class="bg-sky-650 w-0.5 h-2 rounded-full"></div><div class="bg-sky-650 w-0.5 h-3 rounded-full"></div><div class="bg-sky-650 w-0.5 h-4.5 rounded-full"></div><div class="bg-sky-650 w-0.5 h-3 rounded-full"></div><div class="bg-sky-650 w-0.5 h-2 rounded-full"></div></div>'
                                ],
                                'comptia' => [
                                    'bg' => 'bg-emerald-50 border-emerald-250', 
                                    'html' => '<span class="text-xs font-black tracking-tighter text-emerald-700">CompTIA</span>'
                                ],
                                'salesforce' => [
                                    'bg' => 'bg-sky-50 border-sky-100', 
                                    'html' => '<svg class="w-7 h-7 text-sky-500" fill="currentColor" viewBox="0 0 24 24"><path d="M19.35 10.04C18.67 6.59 15.64 4 12 4 9.11 4 6.6 5.64 5.35 8.04 2.34 8.36 0 10.91 0 14c0 3.31 2.69 6 6 6h13c2.76 0 5-2.24 5-5 0-2.64-2.05-4.78-4.65-4.96z"/></svg>'
                                ],
                                'oracle' => [
                                    'bg' => 'bg-red-50 border-red-150', 
                                    'html' => '<span class="text-xs font-serif font-black tracking-tight uppercase text-red-600">Oracle</span>'
                                ],
                                'red-hat' => [
                                    'bg' => 'bg-red-950 border-red-900', 
                                    'html' => '<span class="text-[10px] font-bold font-sans text-white">RedHat</span>'
                                ],
                                'vmware' => [
                                    'bg' => 'bg-slate-50 border-teal-250', 
                                    'html' => '<span class="text-xs font-bold tracking-tight text-teal-600 font-mono">vmware</span>'
                                ],
                                'project-management-institute-pmi' => [
                                    'bg' => 'bg-purple-50 border-purple-200', 
                                    'html' => '<span class="text-xs font-extrabold tracking-tighter text-purple-700">PMI</span>'
                                ],
                                'isaca' => [
                                    'bg' => 'bg-indigo-50 border-indigo-200', 
                                    'html' => '<span class="text-xs font-extrabold tracking-tighter uppercase text-indigo-700">ISACA</span>'
                                ],
                                'itil' => [
                                    'bg' => 'bg-green-50 border-green-200', 
                                    'html' => '<span class="text-xs font-extrabold font-mono tracking-tighter text-green-700">ITIL</span>'
                                ],
                                'palo-alto' => [
                                    'bg' => 'bg-orange-50 border-orange-200', 
                                    'html' => '<span class="text-[9px] font-black tracking-tighter uppercase text-orange-700">PaloAlto</span>'
                                ],
                                'fortinet' => [
                                    'bg' => 'bg-red-50 border-red-200', 
                                    'html' => '<span class="text-xs font-black uppercase text-red-700">Forti</span>'
                                ]
                            ];
                            $style = $logoStyles[$vendor->slug] ?? [
                                'bg' => 'bg-slate-100 border-gray-200', 
                                'html' => '<span class="text-sm font-bold uppercase text-gray-700">' . substr($vendor->name, 0, 2) . '</span>'
                            ];
                        @endphp
                        @if($vendor->logo_path)
                            <div class="h-12 w-12 rounded border flex items-center justify-center group-hover:scale-105 transition bg-white border-gray-200 p-2">
                                <img src="{{ $vendor->logo_path }}" alt="{{ $vendor->name }}" class="max-h-full max-w-full object-contain">
                            </div>
                        @else
                            <div class="h-12 w-12 rounded border flex items-center justify-center group-hover:scale-105 transition {{ $style['bg'] }}">
                                {!! $style['html'] !!}
                            </div>
                        @endif
                        <div>
                            <h3 class="font-bold text-navy text-base mt-1 group-hover:text-cyan transition">{{ $vendor->name }}</h3>
                        </div>
                    </div>
                    
                    <p class="text-sm text-gray-500 mb-6 line-clamp-3">
                        {{ $vendor->description }}
                    </p>

                    <div class="flex justify-between items-center pt-4 border-t border-gray-100">
                        <span class="text-xs font-semibold text-gray-400">{{ $vendor->exam_count }} Study Guides</span>
                        <a href="{{ url('/vendors/' . $vendor->slug) }}" class="text-xs font-bold text-cyan group-hover:text-navy transition flex items-center space-x-1">
                            <span>View Guides</span>
                            <span>&rarr;</span>
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
@endsection
