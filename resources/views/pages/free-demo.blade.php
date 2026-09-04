@extends('layouts.public')

@section('title', 'Download Free Exam PDF Sample - Exam Topics Base')

@section('content')
<!-- Hero Section (Premium Deep Space) -->
<section class="bg-gradient-to-br from-[#07101E] via-navy to-[#0F172A] text-white pt-24 pb-32 relative overflow-hidden">
    <!-- Abstract glowing orbs -->
    <div class="absolute top-0 right-1/4 w-96 h-96 bg-cyan rounded-full mix-blend-screen filter blur-[120px] opacity-20 animate-pulse"></div>
    <div class="absolute bottom-0 left-1/4 w-96 h-96 bg-purple-600 rounded-full mix-blend-screen filter blur-[120px] opacity-20"></div>
    <!-- Abstract Tech Lines -->
    <div class="absolute inset-0 opacity-10 pointer-events-none" style="background-image: linear-gradient(#ffffff 1px, transparent 1px), linear-gradient(90deg, #ffffff 1px, transparent 1px); background-size: 64px 64px;"></div>

    <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <div class="inline-flex items-center space-x-2 bg-white/5 border border-white/10 rounded-full px-4 py-1.5 mb-6">
            <svg class="w-4 h-4 text-cyan" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
            <span class="text-xs font-bold uppercase tracking-widest text-gray-300">100% Free Demo</span>
        </div>
        <h1 class="text-4xl md:text-5xl lg:text-6xl font-black tracking-tight mb-6 leading-tight">Try Before You <span class="text-transparent bg-clip-text bg-gradient-to-r from-cyan to-blue-500">Buy</span></h1>
        <p class="text-lg text-gray-300 max-w-2xl mx-auto font-light leading-relaxed">
            Get a free sample containing 10 verified practice questions with explanations delivered directly to your email instantly.
        </p>
    </div>
</section>

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
