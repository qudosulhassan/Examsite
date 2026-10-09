@extends('layouts.public')

@section('title', 'Download Free Exam PDF Sample - ' . ($currentSite->name ?? ($globalSettings['site_name'] ?? config('app.name', 'ProExamTopics'))))

@section('content')
<!-- Premium Demo Product Hero -->
<x-site-b.hero variant="demo" glowColor="cyan" padding="compact">
    <div class="relative max-w-4xl mx-auto text-center">
        <!-- Floating Simulator Timer Card (Left) -->
        <div class="cpm-float hidden md:flex absolute -left-8 top-4 items-center gap-2.5 px-3.5 py-2 rounded-2xl bg-slate-900/90 border border-slate-700/80 shadow-xl backdrop-blur-md">
            <span class="w-2.5 h-2.5 rounded-full bg-emerald-400 animate-pulse"></span>
            <div class="text-left">
                <div class="text-[10px] text-slate-400 font-mono-code uppercase">Time Remaining</div>
                <div class="text-xs font-black text-white font-mono-code">01:45:00</div>
            </div>
        </div>

        <!-- Floating Question Card (Right) -->
        <div class="cpm-float-reverse hidden md:flex absolute -right-8 top-8 items-center gap-2.5 px-3.5 py-2 rounded-2xl bg-slate-900/90 border border-slate-700/80 shadow-xl backdrop-blur-md">
            <div class="text-left">
                <div class="text-[10px] text-cyan-300 font-mono-code uppercase">Demo Question 1 of 10</div>
                <div class="text-xs font-bold text-white">Cloud Architecture Core</div>
            </div>
        </div>

        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-slate-800/80 border border-slate-700/60 text-xs font-semibold text-slate-300 mb-4 backdrop-blur-sm shadow-sm">
            <span class="w-2 h-2 rounded-full bg-[#00D4AA] animate-pulse"></span>
            <span class="font-mono-code text-[#00D4AA] font-bold">100% FREE SAMPLE</span>
            <span class="text-slate-500">|</span>
            <span>Instant Web Simulator Preview</span>
        </div>

        <h1 class="text-3xl sm:text-4xl lg:text-5xl font-black text-white tracking-tight mb-3 leading-tight">
            Experience the Test Engine <span class="bg-gradient-to-r from-[#00D4AA] via-cyan-300 to-blue-400 bg-clip-text text-transparent">Before You Buy</span>
        </h1>
        <p class="text-sm sm:text-base text-slate-300 max-w-xl mx-auto mb-6 leading-relaxed">
            Get an instant free sample containing 10 verified practice questions with full topic explanations delivered directly to your inbox.
        </p>

        <div>
            <a href="#demo-form" class="inline-flex items-center gap-2 bg-[#2563eb] hover:bg-blue-600 text-white font-black px-7 py-3 rounded-xl text-sm shadow-xl transition transform hover:-translate-y-0.5">
                <span>Start Free Demo</span>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
            </a>
        </div>
    </div>
</x-site-b.hero>

<!-- Demo Capture Form -->
<section class="py-16 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 grid grid-cols-1 lg:grid-cols-2 gap-8 items-stretch relative z-20 -mt-20">
        
        <!-- Left: Branding & Info -->
        <div class="bg-white border border-gray-100 rounded-3xl p-10 shadow-[0_10px_40px_rgba(0,0,0,0.04)] h-full flex flex-col">
            <h3 class="text-2xl font-black text-navy mb-4">What's in the Demo?</h3>
            <p class="text-sm font-medium text-gray-500 leading-relaxed mb-8">Our free demo guide gives you a sneak peek into the quality of our premium material. Unlike other platforms, we never hide our detailed explanations behind watermarks. You'll receive:</p>
            
            <ul class="space-y-6 text-[13px] text-navy font-bold mb-8 flex-1">
                <li class="flex items-start">
                    <div class="mt-0.5 bg-cyan/10 p-1 rounded-md text-cyan mr-4"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg></div>
                    <span class="leading-relaxed">10 real exam questions with multiple-choice options</span>
                </li>
                <li class="flex items-start">
                    <div class="mt-0.5 bg-cyan/10 p-1 rounded-md text-cyan mr-4"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg></div>
                    <span class="leading-relaxed">Verified correct answers marked clearly</span>
                </li>
                <li class="flex items-start">
                    <div class="mt-0.5 bg-cyan/10 p-1 rounded-md text-cyan mr-4"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg></div>
                    <span class="leading-relaxed">Full text explanations of core technical topics</span>
                </li>
                <li class="flex items-start">
                    <div class="mt-0.5 bg-cyan/10 p-1 rounded-md text-cyan mr-4"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg></div>
                    <span class="leading-relaxed">100% formatted PDF compatible with mobile and print</span>
                </li>
            </ul>

            <div class="p-6 bg-gray-50 border border-gray-100 rounded-2xl mt-auto">
                <p class="font-black text-navy text-sm mb-2 flex items-center"><svg class="w-4 h-4 text-cyan mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg> No Credit Card Required</p>
                <p class="text-xs font-medium text-gray-500 leading-relaxed">We respect your privacy. We will never share your email address. You will receive one email containing a secure download link active for 24 hours.</p>
            </div>
        </div>

        <!-- Right: Form -->
        <div class="bg-white border border-gray-100 rounded-3xl p-10 shadow-[0_20px_50px_rgba(0,0,0,0.08)] relative overflow-hidden h-full flex flex-col justify-center">
            <!-- decorative background -->
            <div class="absolute top-0 right-0 w-32 h-32 bg-cyan rounded-full mix-blend-multiply filter blur-[50px] opacity-10 pointer-events-none"></div>

            <h3 class="text-2xl font-black text-navy mb-8 relative z-10">Request Demo</h3>
            
            @if(session('status'))
                <div class="mb-8 bg-green-50 border-l-4 border-green-400 text-green-700 p-4 rounded-r text-[13px] font-bold shadow-sm relative z-10">
                    {{ session('status') }}
                </div>
            @endif

            <form action="{{ url('/free-demo') }}" method="POST" class="space-y-6 relative z-10">
                @csrf
                <div>
                    <label class="block text-[11px] font-black uppercase tracking-widest text-gray-400 mb-2">Your Full Name</label>
                    <input type="text" name="name" required placeholder="John Doe" class="w-full px-5 py-4 rounded-xl border border-gray-200 text-sm font-medium focus:outline-none focus:border-cyan focus:ring-1 focus:ring-cyan bg-gray-50/50 hover:bg-gray-50 transition-colors">
                </div>

                <div>
                    <label class="block text-[11px] font-black uppercase tracking-widest text-gray-400 mb-2">Email Address</label>
                    <input type="email" name="email" required placeholder="john@example.com" class="w-full px-5 py-4 rounded-xl border border-gray-200 text-sm font-medium focus:outline-none focus:border-cyan focus:ring-1 focus:ring-cyan bg-gray-50/50 hover:bg-gray-50 transition-colors">
                </div>

                <div>
                    <label class="block text-[11px] font-black uppercase tracking-widest text-gray-400 mb-2">Select Certification Exam</label>
                    <select name="exam_id" required class="w-full px-5 py-4 rounded-xl border border-gray-200 text-sm font-medium focus:outline-none focus:border-cyan focus:ring-1 focus:ring-cyan bg-gray-50/50 hover:bg-gray-50 transition-colors text-navy appearance-none">
                        <option value="">Choose an exam...</option>
                        @foreach($exams as $exam)
                            <option value="{{ $exam->id }}">
                                {{ $exam->exam_code }} - {{ $exam->exam_name }} ({{ $exam->vendor ? $exam->vendor->name : '' }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <button type="submit" class="w-full bg-navy hover:bg-gradient-to-r hover:from-cyan hover:to-blue-500 text-white font-black py-4 rounded-xl shadow-[0_10px_20px_rgba(0,0,0,0.1)] hover:shadow-[0_10px_30px_rgba(0,212,170,0.3)] transition-all duration-300 text-sm mt-4">
                    Send My Free Demo
                </button>
            </form>
        </div>
    </div>
</section>
@endsection
